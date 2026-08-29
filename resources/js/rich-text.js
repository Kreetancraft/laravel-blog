import { Editor, Node, mergeAttributes } from '@tiptap/core';
import { StarterKit } from '@tiptap/starter-kit';
import { TextStyle } from '@tiptap/extension-text-style';
import { Color } from '@tiptap/extension-color';
import { Highlight } from '@tiptap/extension-highlight';
import { Image } from '@tiptap/extension-image';
import { TextAlign } from '@tiptap/extension-text-align';
import { Placeholder } from '@tiptap/extension-placeholder';
import { TableKit } from '@tiptap/extension-table';
import { TaskList, TaskItem } from '@tiptap/extension-list';
import { CharacterCount } from '@tiptap/extension-character-count';
import { Youtube } from '@tiptap/extension-youtube';

/**
 * A callout: note, tip, warning or danger.
 *
 * Rendered as a plain <div data-callout="..."> so the published HTML needs no
 * JavaScript to read, and a sanitiser that strips unknown elements leaves it
 * alone. The type lives in an attribute rather than a class because classes are
 * the first thing a purifier rewrites.
 */
const Callout = Node.create({
    name: 'callout',
    group: 'block',
    content: 'block+',
    defining: true,

    addAttributes() {
        return {
            type: {
                default: 'note',
                parseHTML: (element) => element.getAttribute('data-callout') || 'note',
                renderHTML: (attributes) => ({ 'data-callout': attributes.type }),
            },
        };
    },

    parseHTML() {
        return [{ tag: 'div[data-callout]' }];
    },

    renderHTML({ HTMLAttributes }) {
        return ['div', mergeAttributes(HTMLAttributes, { class: 'rt-callout' }), 0];
    },

    addCommands() {
        return {
            setCallout:
                (type = 'note') =>
                ({ commands }) =>
                    commands.wrapIn(this.name, { type }),
            toggleCallout:
                (type = 'note') =>
                ({ commands }) =>
                    commands.toggleWrap(this.name, { type }),
            unsetCallout:
                () =>
                ({ commands }) =>
                    commands.lift(this.name),
        };
    },
});

/**
 * Strip the wrapper markup Word and Google Docs paste.
 *
 * Both paste a document, not a fragment: <o:p> tags, mso-* styles, a <style>
 * block, and a class on every element. Left alone it lands in the database and
 * then on the public site. Structure — headings, lists, links, emphasis — is
 * kept; presentation is dropped, because the site's own styles should win.
 */
function cleanPastedHtml(html) {
    if (! html || ! /(class="?Mso|<o:p|urn:schemas-microsoft-com|docs-internal-guid)/i.test(html)) {
        return html;
    }

    const doc = new DOMParser().parseFromString(html, 'text/html');

    doc.querySelectorAll('style, meta, link, xml, o\\:p').forEach((el) => el.remove());

    doc.querySelectorAll('*').forEach((el) => {
        el.removeAttribute('class');
        el.removeAttribute('lang');
        el.removeAttribute('style');

        // Google Docs wraps everything in <b style="font-weight:normal">, which
        // would otherwise make the whole paste bold.
        if (el.tagName === 'B' && ! el.textContent.trim()) {
            el.remove();
        }
    });

    // <span> with nothing left on it is noise around the text.
    doc.querySelectorAll('span').forEach((el) => el.replaceWith(...el.childNodes));

    return doc.body.innerHTML;
}

/**
 * Every text match in the document, with absolute positions.
 *
 * Walks the ProseMirror doc rather than the HTML: positions are what the
 * replace commands need, and going through HTML would lose them.
 */
function findMatches(editor, needle, { matchCase = false } = {}) {
    if (! editor || ! needle) {
        return [];
    }

    const matches = [];
    const search = matchCase ? needle : needle.toLowerCase();

    editor.state.doc.descendants((node, pos) => {
        if (! node.isText) {
            return;
        }

        const text = matchCase ? node.text : node.text.toLowerCase();
        let index = text.indexOf(search);

        while (index !== -1) {
            matches.push({ from: pos + index, to: pos + index + needle.length });
            index = text.indexOf(search, index + needle.length);
        }
    });

    return matches;
}

/**
 * Tiptap-powered rich text editor as an Alpine factory.
 *
 * Exposed on `window` (not via Alpine.data + alpine:init) so it resolves
 * regardless of whether this module runs before or after Flux/Livewire start
 * Alpine. Content syncs to the Livewire model via $wire.get/$wire.set, so the
 * server still receives (and can sanitise) the same HTML string.
 *
 * The Tiptap editor instance is kept in this `editor` closure variable, NOT in
 * a reactive Alpine property: a reactive Proxy around the editor breaks
 * ProseMirror's internal state-identity checks ("Applying a mismatched
 * transaction"). Only plain UI state (active flags, content, menus) is reactive.
 */
function richText(model, placeholder = 'Write here…') {
    let editor = null;

    return {
        uid: 'rt-' + Math.random().toString(36).slice(2),
        content: '',
        focused: false,
        showSource: false,
        fullscreen: false,
        linkOpen: false,
        colorOpen: false,
        hiliteOpen: false,
        headingOpen: false,
        alignOpen: false,
        insertOpen: false,
        tableOpen: false,
        embedOpen: false,
        findOpen: false,
        linkUrl: '',
        embedUrl: '',
        findTerm: '',
        replaceTerm: '',
        findMatchCase: false,
        findCount: 0,
        words: 0,
        characters: 0,
        active: {},

        closeMenus() {
            this.colorOpen = this.hiliteOpen = this.headingOpen = false;
            this.alignOpen = this.insertOpen = this.tableOpen = this.embedOpen = false;
        },

        init() {
            // Defensive: never mount twice on the same host (a re-run init would
            // create a second ProseMirror view → "mismatched transaction").
            if (this.$refs.editor.querySelector('.tiptap')) {
                return;
            }

            // Read the current HTML as a plain string. We sync back with
            // $wire.set(...false) on edit (deferred — no per-keystroke request).
            const initial = this.$wire.get(model);
            this.content = typeof initial === 'string' ? initial : '';

            editor = new Editor({
                element: this.$refs.editor,
                extensions: [
                    StarterKit.configure({
                        heading: { levels: [1, 2, 3] },
                        link: { openOnClick: false, autolink: true },
                    }),
                    TextStyle,
                    Color,
                    Highlight.configure({ multicolor: true }),
                    Image,
                    TextAlign.configure({ types: ['heading', 'paragraph'] }),
                    Placeholder.configure({ placeholder }),
                    TableKit.configure({ table: { resizable: true } }),
                    TaskList,
                    TaskItem.configure({ nested: true }),
                    CharacterCount,
                    Youtube.configure({ controls: true, nocookie: true }),
                    Callout,
                ],
                content: this.content,
                editorProps: {
                    attributes: { class: 'tiptap focus:outline-none' },
                    transformPastedHTML: (html) => cleanPastedHtml(html),
                },
                // NOTE: these callbacks fire during `new Editor(...)`, before the
                // `editor =` assignment completes — always use the callback's
                // `editor` argument, never the closure (still null at that point).
                onUpdate: ({ editor }) => {
                    this.content = editor.getHTML();
                    this.syncToWire();
                    this.refreshActive(editor);
                },
                onFocus: () => { this.focused = true; },
                onBlur: () => { this.focused = false; },
                onSelectionUpdate: ({ editor }) => this.refreshActive(editor),
                onTransaction: ({ editor }) => this.refreshActive(editor),
            });

            this.refreshActive(editor);
        },

        destroy() {
            editor && editor.destroy();
        },

        syncToWire() {
            this.$wire.set(model, this.content, false);
        },

        chain() {
            return editor.chain().focus();
        },

        refreshActive(ed) {
            const e = ed || editor;
            if (! e) {
                return;
            }
            const headingLevel = e.isActive('heading', { level: 1 }) ? 1
                : e.isActive('heading', { level: 2 }) ? 2
                : e.isActive('heading', { level: 3 }) ? 3
                : 0;
            const align = e.isActive({ textAlign: 'center' }) ? 'center'
                : e.isActive({ textAlign: 'right' }) ? 'right'
                : 'left';

            this.active = {
                bold: e.isActive('bold'),
                italic: e.isActive('italic'),
                underline: e.isActive('underline'),
                strike: e.isActive('strike'),
                headingLevel,
                headingLabel: headingLevel ? 'Heading ' + headingLevel : 'Normal',
                bulletList: e.isActive('bulletList'),
                orderedList: e.isActive('orderedList'),
                taskList: e.isActive('taskList'),
                blockquote: e.isActive('blockquote'),
                codeBlock: e.isActive('codeBlock'),
                link: e.isActive('link'),
                highlight: e.isActive('highlight'),
                table: e.isActive('table'),
                callout: e.isActive('callout'),
                align,
            };

            const counter = e.storage.characterCount;
            if (counter) {
                this.words = counter.words();
                this.characters = counter.characters();
            }
        },

        // ── Marks & blocks ────────────────────────────────────────────────
        toggleBold() { this.chain().toggleBold().run(); },
        toggleItalic() { this.chain().toggleItalic().run(); },
        toggleUnderline() { this.chain().toggleUnderline().run(); },
        toggleStrike() { this.chain().toggleStrike().run(); },
        setParagraph() { this.chain().setParagraph().run(); },
        toggleHeading(level) { this.chain().toggleHeading({ level }).run(); },
        setBlock(level) { level ? this.chain().setHeading({ level }).run() : this.chain().setParagraph().run(); },
        toggleBulletList() { this.chain().toggleBulletList().run(); },
        toggleOrderedList() { this.chain().toggleOrderedList().run(); },
        toggleTaskList() { this.chain().toggleTaskList().run(); },
        toggleBlockquote() { this.chain().toggleBlockquote().run(); },
        toggleCodeBlock() { this.chain().toggleCodeBlock().run(); },
        setAlign(direction) { this.chain().setTextAlign(direction).run(); },
        sinkListItem() { this.chain().sinkListItem('listItem').run(); },
        liftListItem() { this.chain().liftListItem('listItem').run(); },
        horizontalRule() { this.chain().setHorizontalRule().run(); },
        clearFormatting() { this.chain().unsetAllMarks().clearNodes().run(); },
        undo() { this.chain().undo().run(); },
        redo() { this.chain().redo().run(); },

        // ── Callouts ──────────────────────────────────────────────────────
        setCallout(type) { this.chain().toggleCallout(type).run(); this.insertOpen = false; },

        // ── Tables ────────────────────────────────────────────────────────
        insertTable() {
            this.chain().insertTable({ rows: 3, cols: 3, withHeaderRow: true }).run();
            this.insertOpen = false;
        },
        addColumnBefore() { this.chain().addColumnBefore().run(); },
        addColumnAfter() { this.chain().addColumnAfter().run(); },
        deleteColumn() { this.chain().deleteColumn().run(); },
        addRowBefore() { this.chain().addRowBefore().run(); },
        addRowAfter() { this.chain().addRowAfter().run(); },
        deleteRow() { this.chain().deleteRow().run(); },
        toggleHeaderRow() { this.chain().toggleHeaderRow().run(); },
        mergeOrSplit() { this.chain().mergeOrSplit().run(); },
        deleteTable() { this.chain().deleteTable().run(); this.tableOpen = false; },

        // ── Video embeds ──────────────────────────────────────────────────
        openEmbed() {
            this.embedUrl = '';
            this.closeMenus();
            this.embedOpen = true;
            this.$nextTick(() => this.$refs.embedInput && this.$refs.embedInput.focus());
        },
        applyEmbed() {
            const url = (this.embedUrl || '').trim();
            if (url) {
                this.chain().setYoutubeVideo({ src: url }).run();
            }
            this.embedUrl = '';
            this.embedOpen = false;
        },

        // ── Find & replace ────────────────────────────────────────────────
        toggleFind() {
            this.findOpen = ! this.findOpen;
            if (this.findOpen) {
                this.$nextTick(() => this.$refs.findInput && this.$refs.findInput.focus());
            }
        },
        runFind() {
            const matches = findMatches(editor, this.findTerm, { matchCase: this.findMatchCase });
            this.findCount = matches.length;

            if (matches.length) {
                // Select the first hit so the editor scrolls to it.
                this.chain().setTextSelection(matches[0]).run();
            }
        },
        replaceAll() {
            const matches = findMatches(editor, this.findTerm, { matchCase: this.findMatchCase });

            if (! matches.length) {
                this.findCount = 0;
                return;
            }

            // Last to first: replacing forwards would shift every position
            // after the one just written.
            const chain = editor.chain().focus();

            matches.reverse().forEach((match) => {
                chain.insertContentAt(match, this.replaceTerm || '');
            });

            chain.run();
            this.findCount = 0;
        },

        // ── Color & highlight ─────────────────────────────────────────────
        setColor(color) { this.chain().setColor(color).run(); this.colorOpen = false; },
        setHighlight(color) { this.chain().toggleHighlight({ color }).run(); this.hiliteOpen = false; },

        // ── Links ─────────────────────────────────────────────────────────
        openLink() {
            if (! editor) {
                return;
            }
            this.linkUrl = editor.getAttributes('link').href || '';
            this.colorOpen = this.hiliteOpen = false;
            this.linkOpen = true;
            this.$nextTick(() => this.$refs.linkInput && this.$refs.linkInput.focus());
        },
        applyLink() {
            const url = this.linkUrl.trim();
            if (url) {
                this.chain().extendMarkRange('link').setLink({ href: url }).run();
            } else {
                this.chain().extendMarkRange('link').unsetLink().run();
            }
            this.linkOpen = false;
        },
        unsetLink() { this.chain().unsetLink().run(); },

        // ── Media-picker image insertion ──────────────────────────────────
        openImage() {
            window.__rtImageTarget = this.uid;
            this.insertOpen = false;
            this.$nextTick(() => {
                try {
                    if (window.Flux) window.Flux.modal('media-picker-rich-text-image').show();
                } catch (e) {}
            });
        },
        insertImages(detail) {
            if (! detail || detail.group !== 'rich-text-image' || window.__rtImageTarget !== this.uid) {
                return;
            }
            (detail.items || []).forEach((item) => {
                if (item.url) this.chain().setImage({ src: item.url }).run();
            });
            window.__rtImageTarget = null;
        },

        // ── Source view ───────────────────────────────────────────────────
        toggleSource() {
            this.showSource = ! this.showSource;
            if (! this.showSource && editor) {
                const html = typeof this.content === 'string' ? this.content : '';
                editor.commands.setContent(html, { emitUpdate: false });
                this.syncToWire();
            }
        },
    };
}

window.richText = richText;

// Exported for tests and for a host that wants to reuse the paste cleanup.
window.richTextHelpers = { cleanPastedHtml, findMatches };
