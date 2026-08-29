/**
 * The Alpine factories the shipped form components need.
 *
 * `x-blog::date-picker` and `x-blog::number-input` came out of an application
 * whose app.js defined `window.datePicker` and `window.numberInput`. Shipping
 * the Blade without the JavaScript left both inert in every other host — the
 * same defect the editor had in 0.1.0, missed on the other two components.
 *
 * Plain Alpine, no dependencies: they ride along in the editor bundle rather
 * than asking a host for a build step.
 */

/**
 * Calendar dropdown that reads and writes through a Livewire model.
 */
window.datePicker = function datePicker(config) {
    return {
        isOpen: false,
        selected: null,
        display: '',
        month: new Date().getMonth(),
        year: new Date().getFullYear(),
        monthNames: [
            'January', 'February', 'March', 'April', 'May', 'June',
            'July', 'August', 'September', 'October', 'November', 'December',
        ],
        dayHeaders: ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'],

        init() {
            // Read the component state, not $refs.hidden: Livewire renders no
            // value attribute for wire:model inputs — it hydrates them after
            // boot — so the hidden input is still empty when Alpine starts, and
            // every saved date rendered as an empty "Pick a date".
            const raw = this.$wire ? this.$wire.get(config.model) : this.$refs.hidden?.value;

            if (raw) {
                // Tolerate a full datetime as well as a plain Y-m-d: casts
                // differ between models.
                const d = new Date(String(raw).slice(0, 10) + 'T00:00:00');

                if (! isNaN(d.getTime())) {
                    this.selected = d;
                    this.display = this.formatDate(d);
                    this.month = d.getMonth();
                    this.year = d.getFullYear();
                }
            }
        },

        get monthYear() {
            return `${this.monthNames[this.month]} ${this.year}`;
        },

        get days() {
            const firstDay = new Date(this.year, this.month, 1).getDay();
            const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
            const days = [];
            // Monday-first: JS weeks start on Sunday.
            const mondayStart = (firstDay + 6) % 7;

            for (let i = 0; i < mondayStart; i++) days.push(0);
            for (let i = 1; i <= daysInMonth; i++) days.push(i);

            return days;
        },

        isSelected(day) {
            if (! this.selected) return false;

            return new Date(this.year, this.month, day).toDateString() === this.selected.toDateString();
        },

        isDisabled(day) {
            const d = new Date(this.year, this.month, day);

            if (config.min) {
                const minDate = new Date(config.min + 'T00:00:00');
                if (! isNaN(minDate.getTime()) && d < minDate) return true;
            }

            if (config.max) {
                const maxDate = new Date(config.max + 'T00:00:00');
                if (! isNaN(maxDate.getTime()) && d > maxDate) return true;
            }

            return false;
        },

        selectDate(day) {
            if (this.isDisabled(day)) return;

            this.selected = new Date(this.year, this.month, day);
            this.display = this.formatDate(this.selected);
            this.isOpen = false;
            this.$wire.set(config.model, this.display);
        },

        prevMonth() {
            if (this.month === 0) { this.month = 11; this.year--; } else { this.month--; }
        },

        nextMonth() {
            if (this.month === 11) { this.month = 0; this.year++; } else { this.month++; }
        },

        today() {
            const now = new Date();

            this.selected = now;
            this.display = this.formatDate(now);
            this.month = now.getMonth();
            this.year = now.getFullYear();
            this.isOpen = false;
            this.$wire.set(config.model, this.display);
        },

        clear() {
            this.selected = null;
            this.display = '';
            this.isOpen = false;
            this.$wire.set(config.model, null);
        },

        formatDate(date) {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');

            return `${y}-${m}-${d}`;
        },
    };
};

/**
 * Number field with its own increment and decrement, no native spinners.
 */
window.numberInput = function numberInput(config) {
    return {
        value: '',

        get atMin() {
            if (config.min === null) return false;
            const num = parseFloat(this.value);

            return ! isNaN(num) && num <= config.min;
        },

        get atMax() {
            if (config.max === null) return false;
            const num = parseFloat(this.value);

            return ! isNaN(num) && num >= config.max;
        },

        init() {
            // Same reason as the date picker: the hidden input is empty at init.
            const initial = this.$wire ? this.$wire.get(config.model) : this.$refs.hidden?.value;

            if (initial !== null && initial !== undefined && initial !== '') {
                const num = parseFloat(initial);
                this.value = isNaN(num) ? initial : num;
            }
        },

        // live:false defers to the next Livewire request rather than firing one
        // per keystroke, which matters on rate-limited admin routes.
        sync(live = true) {
            this.$wire.set(config.model, this.value === '' ? null : this.value, live);
        },

        handleInput() {
            // Never clamp mid-typing: entering "12" in a min:5 field would clamp
            // the intermediate "1" to "5". Clamping happens on blur.
            this.sync(false);
        },

        handleBlur() {
            if (this.value === '' || this.value === undefined || this.value === null) {
                if (config.min !== null) {
                    this.value = config.min;
                    this.sync();
                }

                return;
            }

            let num = parseFloat(this.value);

            if (isNaN(num)) {
                this.value = config.min ?? '';
                this.sync();

                return;
            }

            if (config.min !== null) num = Math.max(num, config.min);
            if (config.max !== null) num = Math.min(num, config.max);
            if (Number.isInteger(config.step)) num = Math.round(num);

            this.value = num;
            this.sync();
        },

        increment() {
            const current = parseFloat(this.value) || 0;
            let next = current + config.step;

            if (config.max !== null) next = Math.min(next, config.max);
            if (Number.isInteger(config.step)) next = Math.round(next);

            this.value = next;
            this.sync();
        },

        decrement() {
            const current = parseFloat(this.value) || 0;
            let next = current - config.step;

            if (config.min !== null) next = Math.max(next, config.min);
            if (Number.isInteger(config.step)) next = Math.round(next);

            this.value = next;
            this.sync();
        },
    };
};
