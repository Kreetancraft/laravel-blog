@props(['model', 'label' => null, 'description' => null, 'rows' => 6])

@once
    <style>
        [data-rich-text] { line-height: 1.6; }
        [data-rich-text] .tiptap { min-height: var(--rt-min, 12.5rem); padding: .65rem .85rem; outline: none; }
        [data-rich-text] h1 { font-size: 1.6rem; font-weight: 800; margin: .7rem 0 .45rem; letter-spacing: -.01em; }
        [data-rich-text] h2 { font-size: 1.35rem; font-weight: 700; margin: .6rem 0 .4rem; }
        [data-rich-text] h3 { font-size: 1.15rem; font-weight: 600; margin: .5rem 0 .35rem; }
        [data-rich-text] h4 { font-size: 1rem; font-weight: 600; margin: .5rem 0 .3rem; }
        [data-rich-text] p { margin: .35rem 0; }
        [data-rich-text] ul { list-style: disc; padding-left: 1.6rem; margin: .35rem 0; }
        [data-rich-text] ol { list-style: decimal; padding-left: 1.6rem; margin: .35rem 0; }
        [data-rich-text] a { color: #2563eb; text-decoration: underline; }
        [data-rich-text] blockquote { border-left: 3px solid #d4d4d8; padding-left: .9rem; margin: .5rem 0; color: #71717a; font-style: italic; }
        [data-rich-text] pre { background: #f4f4f5; border-radius: .5rem; padding: .75rem; font-family: ui-monospace, monospace; font-size: .85em; overflow-x: auto; white-space: pre-wrap; }
        .dark [data-rich-text] pre { background: #27272a; }
        [data-rich-text] hr { border: 0; border-top: 1px solid #d4d4d8; margin: .75rem 0; }
        [data-rich-text] img { max-width: 100%; height: auto; border-radius: .5rem; margin: .35rem 0; }
        [data-rich-text] mark { border-radius: .15rem; padding: 0 .1rem; }
        [data-rich-text] .tiptap p.is-editor-empty:first-child::before { content: attr(data-placeholder); color: #a1a1aa; float: left; height: 0; pointer-events: none; }
        .rt-btn { display: inline-flex; align-items: center; justify-content: center; height: 1.875rem; min-width: 1.875rem; padding: 0 .4rem; border-radius: .5rem; font-size: .82rem; color: #52525b; cursor: pointer; user-select: none; transition: background-color .12s, color .12s; }
        .rt-btn:hover { background: #f4f4f5; color: #18181b; }
        .dark .rt-btn { color: #d4d4d8; }
        .dark .rt-btn:hover { background: #3f3f46; color: #fff; }
        .rt-btn.is-active { background: #e4e4e7; color: #18181b; }
        .dark .rt-btn.is-active { background: #52525b; color: #fff; }
        .rt-divider { width: 1px; align-self: stretch; margin: .15rem .2rem; background: #e4e4e7; }
        .dark .rt-divider { background: #3f3f46; }
        .rt-menu-item { display: flex; width: 100%; align-items: center; justify-content: space-between; gap: .75rem; border-radius: .375rem; padding: .35rem .55rem; text-align: left; font-size: .85rem; color: #3f3f46; cursor: pointer; }
        .rt-menu-item:hover { background: #f4f4f5; }
        .dark .rt-menu-item { color: #e4e4e7; }
        .dark .rt-menu-item:hover { background: #3f3f46; }
    </style>
@endonce

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif
    @if ($description)
        <flux:text size="sm" class="mb-2 text-zinc-500 dark:text-zinc-400">{{ $description }}</flux:text>
    @endif

    {{-- wire:ignore is on the x-data root so Livewire never re-initializes the
         editor (a second Editor on one host = "mismatched transaction"). --}}
    <div
        wire:ignore
        x-data="richText(@js($model), @js(__('Write here…')))"
        x-on:media-picked.window="insertImages($event.detail)"
        x-on:keydown.escape="linkOpen = false; closeMenus()"
    >
        <div
            x-bind:class="fullscreen ? 'fixed inset-0 z-50 m-0 rounded-none border-0 p-3 bg-white dark:bg-zinc-900' : ''"
            class="flex flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white transition-all duration-200 focus-within:border-zinc-400 focus-within:ring-1 focus-within:ring-zinc-400/20 dark:border-zinc-700 dark:bg-zinc-900 dark:focus-within:border-zinc-600 dark:focus-within:ring-zinc-600/20"
        >
            {{-- Toolbar --}}
            <div class="flex flex-wrap items-center gap-0.5 border-b border-zinc-200 p-1.5 dark:border-zinc-700" data-test="rich-text-toolbar">
                {{-- Heading dropdown --}}
                {{-- click.outside belongs on this wrapper, not the menu: the trigger
                     opens on mousedown, and the click completing that same press would
                     otherwise register as "outside" the menu and shut it again. --}}
                <div class="relative" x-on:click.outside="headingOpen = false">
                    <button type="button" class="rt-btn gap-1 !px-2" x-on:mousedown.prevent="const o = headingOpen; closeMenus(); headingOpen = ! o">
                        <span class="text-xs font-medium" x-text="active.headingLabel || '{{ __('Normal') }}'">{{ __('Normal') }}</span>
                        <flux:icon name="chevron-down" variant="micro" />
                    </button>
                    <div x-show="headingOpen" style="display:none" class="absolute z-30 mt-1 min-w-[10rem] rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                        @foreach ([0 => __('Normal'), 1 => __('Heading 1'), 2 => __('Heading 2'), 3 => __('Heading 3')] as $lvl => $lbl)
                            <button type="button" class="rt-menu-item" x-on:mousedown.prevent="setBlock({{ $lvl }}); headingOpen = false">
                                <span @class(['font-bold text-lg' => $lvl === 1, 'font-bold' => $lvl === 2, 'font-semibold' => $lvl === 3])>{{ $lbl }}</span>
                                <flux:icon name="check" variant="micro" x-show="active.headingLevel === {{ $lvl }}" style="display:none" />
                            </button>
                        @endforeach
                    </div>
                </div>
                <span class="rt-divider"></span>

                {{-- Inline marks --}}
                <flux:tooltip content="{{ __('Bold') }} ⌘B"><button type="button" class="rt-btn font-bold" x-bind:class="active.bold && 'is-active'" x-on:mousedown.prevent="toggleBold()">B</button></flux:tooltip>
                <flux:tooltip content="{{ __('Italic') }} ⌘I"><button type="button" class="rt-btn italic" x-bind:class="active.italic && 'is-active'" x-on:mousedown.prevent="toggleItalic()">I</button></flux:tooltip>
                <flux:tooltip content="{{ __('Underline') }} ⌘U"><button type="button" class="rt-btn underline" x-bind:class="active.underline && 'is-active'" x-on:mousedown.prevent="toggleUnderline()">U</button></flux:tooltip>
                <flux:tooltip content="{{ __('Strikethrough') }} ⌘⇧S"><button type="button" class="rt-btn line-through" x-bind:class="active.strike && 'is-active'" x-on:mousedown.prevent="toggleStrike()">S</button></flux:tooltip>
                <span class="rt-divider"></span>

                {{-- Text color --}}
                <div class="relative" x-on:click.outside="colorOpen = false">
                    <flux:tooltip content="{{ __('Text color') }}"><button type="button" class="rt-btn" x-on:mousedown.prevent="const o = colorOpen; closeMenus(); colorOpen = ! o"><span class="border-b-2 border-rose-500 leading-none">A</span></button></flux:tooltip>
                    <div x-show="colorOpen" style="display:none" class="absolute z-30 mt-1 flex gap-1 rounded-lg border border-zinc-200 bg-white p-1.5 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                        @foreach (['#18181b', '#ef4444', '#f97316', '#eab308', '#22c55e', '#3b82f6', '#8b5cf6', '#ec4899', '#ffffff'] as $c)
                            <button type="button" x-on:mousedown.prevent="setColor('{{ $c }}')" class="size-5 rounded-full border border-zinc-300 dark:border-zinc-600" style="background: {{ $c }}"></button>
                        @endforeach
                    </div>
                </div>

                {{-- Highlight --}}
                <div class="relative" x-on:click.outside="hiliteOpen = false">
                    <flux:tooltip content="{{ __('Highlight') }}"><button type="button" class="rt-btn" x-bind:class="active.highlight && 'is-active'" x-on:mousedown.prevent="const o = hiliteOpen; closeMenus(); hiliteOpen = ! o"><flux:icon name="paint-brush" variant="micro" /></button></flux:tooltip>
                    <div x-show="hiliteOpen" style="display:none" class="absolute z-30 mt-1 flex gap-1 rounded-lg border border-zinc-200 bg-white p-1.5 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                        @foreach (['#fef08a', '#bbf7d0', '#bfdbfe', '#fbcfe8', '#e9d5ff', '#ffffff'] as $c)
                            <button type="button" x-on:mousedown.prevent="setHighlight('{{ $c }}')" class="size-5 rounded-full border border-zinc-300 dark:border-zinc-600" style="background: {{ $c }}"></button>
                        @endforeach
                    </div>
                </div>
                <span class="rt-divider"></span>

                {{-- Lists & blocks --}}
                <flux:tooltip content="{{ __('Bulleted list') }}"><button type="button" class="rt-btn" x-bind:class="active.bulletList && 'is-active'" x-on:mousedown.prevent="toggleBulletList()"><flux:icon name="list-bullet" variant="micro" /></button></flux:tooltip>
                <flux:tooltip content="{{ __('Numbered list') }}"><button type="button" class="rt-btn" x-bind:class="active.orderedList && 'is-active'" x-on:mousedown.prevent="toggleOrderedList()">1.</button></flux:tooltip>
                <flux:tooltip content="{{ __('Quote') }}"><button type="button" class="rt-btn" x-bind:class="active.blockquote && 'is-active'" x-on:mousedown.prevent="toggleBlockquote()">&ldquo;</button></flux:tooltip>
                <flux:tooltip content="{{ __('Code block') }}"><button type="button" class="rt-btn" x-bind:class="active.codeBlock && 'is-active'" x-on:mousedown.prevent="toggleCodeBlock()"><flux:icon name="code-bracket" variant="micro" /></button></flux:tooltip>
                <span class="rt-divider"></span>

                {{-- Alignment dropdown --}}
                <div class="relative" x-on:click.outside="alignOpen = false">
                    <flux:tooltip content="{{ __('Text align') }}"><button type="button" class="rt-btn gap-0.5 !px-1.5" x-on:mousedown.prevent="const o = alignOpen; closeMenus(); alignOpen = ! o"><flux:icon name="bars-3-bottom-left" variant="micro" /><flux:icon name="chevron-down" variant="micro" /></button></flux:tooltip>
                    <div x-show="alignOpen" style="display:none" class="absolute z-30 mt-1 min-w-[8rem] rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-zinc-700 dark:bg-zinc-800">
                        @foreach (['left' => [__('Left'), 'bars-3-bottom-left'], 'center' => [__('Center'), 'bars-3'], 'right' => [__('Right'), 'bars-3-bottom-right']] as $dir => $meta)
                            <button type="button" class="rt-menu-item" x-on:mousedown.prevent="setAlign('{{ $dir }}'); alignOpen = false">
                                <span class="flex items-center gap-2"><flux:icon name="{{ $meta[1] }}" variant="micro" />{{ $meta[0] }}</span>
                                <flux:icon name="check" variant="micro" x-show="active.align === '{{ $dir }}'" style="display:none" />
                            </button>
                        @endforeach
                    </div>
                </div>
                <span class="rt-divider"></span>

                {{-- Insert --}}
                <flux:tooltip content="{{ __('Link') }} ⌘K"><button type="button" class="rt-btn" x-bind:class="active.link && 'is-active'" x-on:mousedown.prevent="openLink()"><flux:icon name="link" variant="micro" /></button></flux:tooltip>
                <flux:tooltip content="{{ __('Remove link') }}"><button type="button" class="rt-btn" x-on:mousedown.prevent="unsetLink()"><flux:icon name="link-slash" variant="micro" /></button></flux:tooltip>
                @if (config('blog.media_picker_view'))
                    <flux:tooltip content="{{ __('Insert image') }}"><button type="button" class="rt-btn" x-on:mousedown.prevent="openImage()"><flux:icon name="photo" variant="micro" /></button></flux:tooltip>
                @endif
                <flux:tooltip content="{{ __('Horizontal rule') }}"><button type="button" class="rt-btn" x-on:mousedown.prevent="horizontalRule()"><flux:icon name="minus" variant="micro" /></button></flux:tooltip>

                {{-- Utility group (pushed right) --}}
                <span class="ml-auto"></span>
                <flux:tooltip content="{{ __('Clear formatting') }}"><button type="button" class="rt-btn" x-on:mousedown.prevent="clearFormatting()">T<sub>x</sub></button></flux:tooltip>
                <flux:tooltip content="{{ __('Undo') }} ⌘Z"><button type="button" class="rt-btn" x-on:mousedown.prevent="undo()"><flux:icon name="arrow-uturn-left" variant="micro" /></button></flux:tooltip>
                <flux:tooltip content="{{ __('Redo') }} ⌘⇧Z"><button type="button" class="rt-btn" x-on:mousedown.prevent="redo()"><flux:icon name="arrow-uturn-right" variant="micro" /></button></flux:tooltip>
                <flux:tooltip content="{{ __('Source code') }}"><button type="button" class="rt-btn" x-bind:class="showSource && 'is-active'" x-on:click="toggleSource()"><flux:icon name="code-bracket-square" variant="micro" /></button></flux:tooltip>
                <flux:tooltip content="{{ __('Fullscreen') }}"><button type="button" class="rt-btn" x-bind:class="fullscreen && 'is-active'" x-on:click="fullscreen = ! fullscreen"><flux:icon name="arrows-pointing-out" variant="micro" /></button></flux:tooltip>
            </div>

            {{-- Link input row --}}
            <div x-show="linkOpen" style="display:none" class="flex items-center gap-2 border-b border-zinc-200 p-1.5 dark:border-zinc-700">
                <input x-ref="linkInput" type="url" x-model="linkUrl" placeholder="https://example.com" x-on:keydown.enter.prevent="applyLink()" x-on:keydown.escape="linkOpen = false" class="flex-1 rounded-md border border-zinc-200 bg-white px-2 py-1 text-sm text-zinc-800 placeholder-zinc-400 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100 dark:placeholder-zinc-500" />
                <flux:button type="button" size="xs" variant="primary" x-on:mousedown.prevent="applyLink()">{{ __('Apply') }}</flux:button>
                <flux:button type="button" size="xs" variant="ghost" x-on:click="linkOpen = false">{{ __('Cancel') }}</flux:button>
            </div>

            {{-- Editor (Tiptap mounts inside this host) --}}
            <div
                x-show="! showSource"
                x-ref="editor"
                data-rich-text
                class="max-w-none flex-1 overflow-y-auto text-sm text-zinc-800 dark:text-zinc-100"
                style="--rt-min: {{ max($rows * 1.6, 12.5) }}rem; max-height: 500px"
                x-bind:style="fullscreen ? '--rt-min: calc(100vh - 7rem); max-height: calc(100vh - 7rem)' : ''"
            ></div>

            {{-- Source view --}}
            <textarea
                x-show="showSource"
                x-model="content"
                x-on:input="syncToWire()"
                style="display:none; min-height: {{ max($rows * 1.6, 12.5) }}rem; max-height: 500px"
                class="flex-1 resize-none bg-zinc-50 px-3 py-2 font-mono text-xs text-zinc-700 focus:outline-none dark:bg-zinc-800 dark:text-zinc-200"
            ></textarea>
        </div>
    </div>

    <flux:error name="{{ $model }}" />
</flux:field>
