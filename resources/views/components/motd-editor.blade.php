@php
    use CedricKassen\MinecraftServerProperties\Properties\MotdCodes;

    $isDisabled = $isDisabled();
    $serverName = $getServerName() ?? 'Minecraft Server';
    $maxPlayers = $getMaxPlayers();
    $t = fn (string $key) => trans('minecraft-server-properties::strings.motd.' . $key);
@endphp

<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    <script>
        window.mcMotdEditor = function (config) {
            const FORMATS = ['k', 'l', 'm', 'n', 'o'];
            const MAX_LINES = 2;
            const CLIPBOARD_TYPE = 'application/x-minecraft-motd';

            const blank = () => ({ c: null, k: false, l: false, m: false, n: false, o: false });
            const same = (a, b) => a.c === b.c && FORMATS.every((x) => a[x] === b[x]);
            const escape = (text) => text.replace(/[&<>"]/g, (ch) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[ch]);

            // The motd as one entry per UTF-16 code unit (so offsets match the DOM), "\n" separates the lines.
            // Entries are never mutated, which makes a copy of the array a cheap undo snapshot.
            let chars = [];
            let undoStack = [];
            let redoStack = [];
            let lastEdit = null;
            // Format for the next typed text after a toolbar button was used without a selection.
            let pending = null;
            let saved = { start: 0, end: 0 };
            let composing = false;
            let hintTimeout = null;

            // Like minecraft: a color code resets the formats, §r resets everything.
            function parse(text, format = blank()) {
                const result = [];

                for (let i = 0; i < text.length; i++) {
                    const ch = text[i];

                    if (ch === '§' && i + 1 < text.length) {
                        const code = text[i + 1].toLowerCase();

                        if (code in config.colors) {
                            format = { ...blank(), c: code };
                        } else if (FORMATS.includes(code)) {
                            format = { ...format, [code]: true };
                        } else if (code === 'r') {
                            format = blank();
                        } else {
                            // Unknown codes stay as they are.
                            result.push({ t: ch, f: format });
                            continue;
                        }

                        i++;
                        continue;
                    }

                    result.push({ t: ch, f: format });
                }

                return result;
            }

            function serialize(list) {
                let text = '';
                let current = blank();

                for (const { t, f } of list) {
                    // The format carries over to the next line, just like in game.
                    if (t !== '\n' && !same(f, current)) {
                        if ((current.c !== null && f.c === null) || FORMATS.some((x) => current[x] && !f[x])) {
                            text += '§r';
                            current = blank();
                        }

                        if (f.c !== current.c) {
                            text += '§' + f.c;
                            current = { ...blank(), c: f.c };
                        }

                        for (const x of FORMATS) {
                            if (f[x] && !current[x]) {
                                text += '§' + x;
                            }
                        }

                        current = f;
                    }

                    text += t;
                }

                return text;
            }

            function decode(attribute) {
                const [color, formats] = (attribute ?? '|').split('|');
                const format = { ...blank(), c: color || null };

                for (const x of formats ?? '') {
                    format[x] = true;
                }

                return format;
            }

            return {
                state: config.state,
                disabled: config.disabled,
                colors: config.colors,
                defaultColor: config.defaultColor,
                active: blank(),
                mixedColor: false,
                colorsOpen: false,
                symbolsOpen: false,
                showSource: false,
                canUndo: false,
                canRedo: false,
                limitHint: false,
                synced: null,

                init() {
                    this.load(this.state ?? '');

                    this.$watch('state', (value) => {
                        if ((value ?? '') !== this.synced) {
                            this.load(value ?? '');
                        }
                    });

                    this.selectionListener = () => this.selectionChanged();
                    document.addEventListener('selectionchange', this.selectionListener);
                },

                destroy() {
                    document.removeEventListener('selectionchange', this.selectionListener);
                },

                load(text) {
                    // Keep the state as it is, so an unchanged motd isn't saved in a normalized form.
                    chars = parse(text.replace(/\r\n?/g, '\n'));
                    this.synced = text;
                    this.render();
                    this.updateActive();

                    if (this.showSource && document.activeElement !== this.$refs.source) {
                        this.$refs.source.value = text;
                    }
                },

                render() {
                    const lines = [[]];

                    for (const ch of chars) {
                        if (ch.t === '\n') {
                            lines.push([]);
                        } else {
                            lines[lines.length - 1].push(ch);
                        }
                    }

                    let html = '';

                    for (const line of lines) {
                        if (!line.length) {
                            html += '<div><br></div>';
                            continue;
                        }

                        html += '<div>';

                        for (let i = 0; i < line.length;) {
                            const f = line[i].f;
                            let text = '';

                            while (i < line.length && same(line[i].f, f)) {
                                text += line[i++].t;
                            }

                            const styles = [];
                            if (f.c) styles.push('color:' + this.colors[f.c]);
                            if (f.l) styles.push('font-weight:700');
                            if (f.o) styles.push('font-style:italic');
                            if (f.n || f.m) styles.push('text-decoration-line:' + [f.n && 'underline', f.m && 'line-through'].filter(Boolean).join(' '));

                            html += '<span data-f="' + (f.c ?? '') + '|' + FORMATS.filter((x) => f[x]).join('') + '"'
                                + (f.k ? ' class="mcm-obfuscated"' : '')
                                + ' style="' + styles.join(';') + '">' + escape(text) + '</span>';
                        }

                        html += '</div>';
                    }

                    this.$refs.editor.innerHTML = html;
                },

                // Reads the editor back after the browser changed it itself (e.g. while composing text with an IME).
                readEditor() {
                    const result = [];

                    Array.from(this.$refs.editor.childNodes).forEach((line, index) => {
                        if (index > 0) {
                            result.push({ t: '\n', f: blank() });
                        }

                        const walker = document.createTreeWalker(line, NodeFilter.SHOW_TEXT);
                        let node = line.nodeType === Node.TEXT_NODE ? line : walker.nextNode();

                        while (node) {
                            const span = node.parentElement?.closest('[data-f]');
                            const format = span && this.$refs.editor.contains(span)
                                ? decode(span.dataset.f)
                                : (result.length ? result[result.length - 1].f : blank());

                            // Per UTF-16 code unit, iterating the string would yield whole code points.
                            for (let i = 0; i < node.data.length; i++) {
                                result.push({ t: node.data[i], f: format });
                            }

                            node = line.nodeType === Node.TEXT_NODE ? null : walker.nextNode();
                        }
                    });

                    return result;
                },

                offsetOf(node, offset) {
                    const editor = this.$refs.editor;

                    if (!editor.contains(node)) {
                        return null;
                    }

                    const lines = Array.from(editor.childNodes);
                    let base = 0;

                    if (node === editor) {
                        for (let i = 0; i < offset && i < lines.length; i++) {
                            base += lines[i].textContent.length + 1;
                        }

                        return Math.min(base, chars.length);
                    }

                    let line = node;
                    while (line.parentNode !== editor) {
                        line = line.parentNode;
                    }

                    for (const element of lines) {
                        if (element === line) {
                            break;
                        }

                        base += element.textContent.length + 1;
                    }

                    const range = document.createRange();
                    range.setStart(line, 0);
                    range.setEnd(node, offset);

                    return base + range.toString().length;
                },

                position(offset) {
                    const lines = Array.from(this.$refs.editor.childNodes);

                    for (let i = 0; i < lines.length; i++) {
                        const length = lines[i].textContent.length;

                        if (offset <= length || i === lines.length - 1) {
                            if (lines[i].nodeType === Node.TEXT_NODE) {
                                return [lines[i], Math.min(offset, length)];
                            }

                            const walker = document.createTreeWalker(lines[i], NodeFilter.SHOW_TEXT);
                            let node;
                            let last = null;

                            while ((node = walker.nextNode())) {
                                if (offset <= node.length) {
                                    return [node, offset];
                                }

                                offset -= node.length;
                                last = node;
                            }

                            return last ? [last, last.length] : [lines[i], 0];
                        }

                        offset -= length + 1;
                    }

                    return [this.$refs.editor, 0];
                },

                readSelection() {
                    const selection = window.getSelection();

                    if (!selection.rangeCount) {
                        return null;
                    }

                    const range = selection.getRangeAt(0);
                    const start = this.offsetOf(range.startContainer, range.startOffset);
                    const end = this.offsetOf(range.endContainer, range.endOffset);

                    return start === null || end === null ? null : { start: Math.min(start, end), end: Math.max(start, end) };
                },

                select(start, end = start) {
                    this.$refs.editor.focus({ preventScroll: true });

                    const [startNode, startOffset] = this.position(start);
                    const [endNode, endOffset] = this.position(end);
                    const range = document.createRange();
                    range.setStart(startNode, startOffset);
                    range.setEnd(endNode, endOffset);

                    const selection = window.getSelection();
                    selection.removeAllRanges();
                    selection.addRange(range);

                    saved = { start, end };
                },

                selectionChanged() {
                    const selection = this.readSelection();

                    if (!selection) {
                        return;
                    }

                    if (pending && (selection.start !== selection.end || selection.start !== pending.at)) {
                        pending = null;
                    }

                    saved = selection;
                    this.updateActive();
                },

                // Format of new text at the offset, taken from the text before it like in a word processor.
                formatAt(offset) {
                    for (let i = offset - 1; i >= 0; i--) {
                        if (chars[i].t !== '\n') return chars[i].f;
                    }

                    for (let i = offset; i < chars.length; i++) {
                        if (chars[i].t !== '\n') return chars[i].f;
                    }

                    return blank();
                },

                typingFormat(start, end) {
                    if (pending) return pending.f;
                    if (start < end && chars[start].t !== '\n') return chars[start].f;

                    return this.formatAt(start);
                },

                updateActive() {
                    const { start, end } = saved;
                    const selected = chars.slice(start, end).filter((ch) => ch.t !== '\n');

                    if (!selected.length) {
                        this.active = { ...this.typingFormat(start, start) };
                        this.mixedColor = false;

                        return;
                    }

                    const active = { ...blank(), c: selected[0].f.c };
                    for (const x of FORMATS) {
                        active[x] = selected.every((ch) => ch.f[x]);
                    }

                    this.mixedColor = selected.some((ch) => ch.f.c !== active.c);
                    this.active = active;
                },

                record(kind) {
                    const now = Date.now();

                    // Typing and deleting are undone word by word rather than per character.
                    if (['typing', 'delete'].includes(kind) && lastEdit?.kind === kind && now - lastEdit.time < 1000) {
                        lastEdit.time = now;

                        return;
                    }

                    undoStack.push({ chars: chars.slice(), selection: { ...saved } });
                    if (undoStack.length > 100) {
                        undoStack.shift();
                    }

                    redoStack = [];
                    lastEdit = { kind, time: now };
                },

                commit(start, end = start, focus = true) {
                    const text = serialize(chars);
                    this.synced = text;
                    this.state = text;

                    this.render();

                    if (focus) {
                        this.select(start, end);
                    } else {
                        saved = { start, end };
                    }

                    if (this.showSource && document.activeElement !== this.$refs.source) {
                        this.$refs.source.value = text;
                    }

                    this.canUndo = undoStack.length > 0;
                    this.canRedo = redoStack.length > 0;
                    this.updateActive();
                },

                countLineBreaks(start, end) {
                    return chars.filter((ch, i) => ch.t === '\n' && (i < start || i >= end)).length;
                },

                flashLimitHint() {
                    this.limitHint = true;
                    clearTimeout(hintTimeout);
                    hintTimeout = setTimeout(() => this.limitHint = false, 3000);
                },

                replace(start, end, inserted, kind) {
                    let lineBreaks = this.countLineBreaks(start, end);

                    // Pasted text beyond the second line is joined with a space instead.
                    inserted = inserted.map((ch) => ch.t === '\n' && lineBreaks++ >= MAX_LINES - 1 ? { t: ' ', f: ch.f } : ch);

                    this.record(kind);
                    chars = chars.slice(0, start).concat(inserted, chars.slice(end));
                    pending = null;
                    this.commit(start + inserted.length);
                },

                insertText(text, kind, start = saved.start, end = saved.end) {
                    text = text.replace(/\r\n?/g, '\n');

                    if (text.includes('\n') && this.countLineBreaks(start, end) >= MAX_LINES - 1) {
                        this.flashLimitHint();
                    }

                    this.replace(start, end, parse(text, this.typingFormat(start, end)), kind);
                },

                targetSelection(event) {
                    const range = event.getTargetRanges?.()[0];

                    if (range) {
                        const start = this.offsetOf(range.startContainer, range.startOffset);
                        const end = this.offsetOf(range.endContainer, range.endOffset);

                        if (start !== null && end !== null) {
                            return { start: Math.min(start, end), end: Math.max(start, end) };
                        }
                    }

                    return this.readSelection() ?? saved;
                },

                onBeforeInput(event) {
                    if (this.disabled) {
                        event.preventDefault();

                        return;
                    }

                    // Text being composed (IME, some mobile keyboards) can't be intercepted, it is read back afterwards.
                    if (event.isComposing || event.inputType === 'insertCompositionText') {
                        return;
                    }

                    event.preventDefault();

                    const type = event.inputType;
                    let { start, end } = this.targetSelection(event);

                    if (type.startsWith('delete')) {
                        if (start === end) {
                            if (type.includes('Backward')) {
                                start = Math.max(0, start - 1);
                            } else {
                                end = Math.min(chars.length, end + 1);
                            }
                        }

                        if (start !== end) {
                            this.replace(start, end, [], 'delete');
                        }

                        return;
                    }

                    switch (type) {
                        case 'insertText':
                        case 'insertReplacementText':
                        case 'insertFromPaste':
                        case 'insertFromDrop': {
                            const text = event.data ?? event.dataTransfer?.getData('text/plain') ?? '';

                            if (text !== '') {
                                this.insertText(text, type === 'insertText' ? 'typing' : 'paste', start, end);
                            }

                            break;
                        }
                        case 'insertParagraph':
                        case 'insertLineBreak':
                            if (this.countLineBreaks(start, end) >= MAX_LINES - 1) {
                                this.flashLimitHint();
                            } else {
                                this.replace(start, end, [{ t: '\n', f: this.typingFormat(start, end) }], 'enter');
                            }

                            break;
                        case 'historyUndo':
                            this.undo();
                            break;
                        case 'historyRedo':
                            this.redo();
                            break;
                        case 'formatBold':
                            this.toggle('l');
                            break;
                        case 'formatItalic':
                            this.toggle('o');
                            break;
                        case 'formatUnderline':
                            this.toggle('n');
                            break;
                        case 'formatStrikeThrough':
                            this.toggle('m');
                            break;
                    }
                },

                onKeydown(event) {
                    if (!(event.ctrlKey || event.metaKey) || event.altKey) {
                        return;
                    }

                    const key = event.key.toLowerCase();
                    const actions = {
                        z: () => event.shiftKey ? this.redo() : this.undo(),
                        y: () => this.redo(),
                        b: () => this.toggle('l'),
                        i: () => this.toggle('o'),
                        u: () => this.toggle('n'),
                    };

                    if (actions[key]) {
                        event.preventDefault();
                        actions[key]();
                    }
                },

                onCompositionStart() {
                    composing = true;
                    this.record('typing');
                },

                onCompositionEnd() {
                    composing = false;
                    this.syncFromEditor();
                },

                onInput() {
                    if (!composing) {
                        this.syncFromEditor();
                    }
                },

                syncFromEditor() {
                    const selection = this.readSelection() ?? saved;
                    chars = this.readEditor();
                    this.commit(Math.min(selection.start, chars.length), Math.min(selection.end, chars.length));
                },

                onCopy(event, cut = false) {
                    const selection = this.readSelection();

                    if (!selection || selection.start === selection.end) {
                        return;
                    }

                    event.preventDefault();

                    const part = chars.slice(selection.start, selection.end);
                    event.clipboardData.setData('text/plain', part.map((ch) => ch.t).join(''));
                    event.clipboardData.setData(CLIPBOARD_TYPE, serialize(part));

                    if (cut && !this.disabled) {
                        this.replace(selection.start, selection.end, [], 'cut');
                    }
                },

                onPaste(event) {
                    event.preventDefault();

                    if (this.disabled) {
                        return;
                    }

                    const selection = this.readSelection() ?? saved;
                    const formatted = event.clipboardData.getData(CLIPBOARD_TYPE);

                    // Copied from this editor: keep its formatting instead of taking over the one at the caret.
                    const text = formatted ? '§r' + formatted : event.clipboardData.getData('text/plain');

                    if (text) {
                        this.insertText(text, 'paste', selection.start, selection.end);
                    }
                },

                // Toolbar buttons don't take the focus, but the last selectionchange event might still be pending.
                refreshSelection() {
                    const selection = this.readSelection();

                    if (selection) {
                        saved = selection;
                    }

                    return saved;
                },

                applyFormat(change) {
                    if (this.disabled) {
                        return;
                    }

                    const { start, end } = this.refreshSelection();

                    if (start === end) {
                        pending = { at: start, f: change(this.typingFormat(start, end)) };
                        this.select(start);
                        this.updateActive();

                        return;
                    }

                    this.record('format');
                    chars = chars.map((ch, i) => i >= start && i < end && ch.t !== '\n' ? { t: ch.t, f: change(ch.f) } : ch);
                    this.commit(start, end);
                },

                toggle(format) {
                    const { start, end } = this.refreshSelection();
                    const selected = chars.slice(start, end).filter((ch) => ch.t !== '\n');
                    const enable = selected.length ? !selected.every((ch) => ch.f[format]) : !this.typingFormat(start, end)[format];

                    this.applyFormat((f) => ({ ...f, [format]: enable }));
                },

                setColor(color) {
                    this.colorsOpen = false;
                    this.applyFormat((f) => ({ ...f, c: color }));
                },

                clearFormat() {
                    this.applyFormat(() => blank());
                },

                insertSymbol(symbol) {
                    this.symbolsOpen = false;

                    if (!this.disabled) {
                        const { start, end } = this.refreshSelection();
                        this.insertText(symbol, 'symbol', start, end);
                    }
                },

                undo() {
                    if (this.disabled || !undoStack.length) {
                        return;
                    }

                    redoStack.push({ chars, selection: { ...saved } });
                    const snapshot = undoStack.pop();
                    chars = snapshot.chars;
                    lastEdit = null;
                    pending = null;
                    this.commit(snapshot.selection.start, snapshot.selection.end);
                },

                redo() {
                    if (this.disabled || !redoStack.length) {
                        return;
                    }

                    undoStack.push({ chars, selection: { ...saved } });
                    const snapshot = redoStack.pop();
                    chars = snapshot.chars;
                    lastEdit = null;
                    pending = null;
                    this.commit(snapshot.selection.start, snapshot.selection.end);
                },

                toggleSource() {
                    this.showSource = !this.showSource;

                    if (this.showSource) {
                        this.$refs.source.value = this.state ?? '';
                        this.$nextTick(() => this.$refs.source.focus());
                    }
                },

                onSourceInput(value) {
                    this.record('source');
                    chars = parse(value.replace(/\r\n?/g, '\n'));
                    this.commit(0, 0, false);

                    // Keep exactly what was typed, normalizing it would move the caret of the textarea.
                    this.synced = value;
                    this.state = value;
                },
            };
        };
    </script>

    <style>
        .mcm { --mcm-font: Minecraft, Minecraftia, Monocraft, ui-monospace, SFMono-Regular, Menlo, Consolas, 'Segoe UI Symbol', 'Noto Sans Symbols 2', 'Noto Sans Symbols', 'DejaVu Sans', monospace; --mcm-surface: #fff; --mcm-popover: #fff; --mcm-ring: color-mix(in oklab, var(--gray-950, #09090b) 10%, transparent); --mcm-divider: color-mix(in oklab, var(--gray-950, #09090b) 10%, transparent); --mcm-hover: var(--gray-50, #fafafa); --mcm-button: var(--gray-700, #3f3f46); --mcm-on-background: var(--gray-50, #fafafa); --mcm-on: var(--primary-600, #2563eb); --mcm-focus: var(--primary-600, #2563eb); --mcm-hint: var(--warning-600, #d97706); border-radius: 0.5rem; background: var(--mcm-surface); box-shadow: 0 0 0 1px var(--mcm-ring); }
        :is(.dark) .mcm { --mcm-surface: color-mix(in oklab, #fff 5%, transparent); --mcm-popover: var(--gray-900, #18181b); --mcm-ring: color-mix(in oklab, #fff 20%, transparent); --mcm-divider: color-mix(in oklab, #fff 10%, transparent); --mcm-hover: color-mix(in oklab, #fff 5%, transparent); --mcm-button: var(--gray-200, #e4e4e7); --mcm-on-background: color-mix(in oklab, #fff 5%, transparent); --mcm-on: var(--primary-400, #60a5fa); --mcm-focus: var(--primary-500, #3b82f6); --mcm-hint: var(--warning-400, #fbbf24); }
        .mcm:focus-within { box-shadow: 0 0 0 2px var(--mcm-focus); }
        .mcm-toolbar { display: flex; flex-wrap: wrap; align-items: center; gap: 0.25rem; padding: 0.375rem; border-bottom: 1px solid var(--mcm-divider); }
        .mcm-group { display: flex; align-items: center; gap: 0.125rem; }
        .mcm-group + .mcm-group { border-left: 1px solid var(--mcm-divider); padding-left: 0.25rem; }
        .mcm-btn { position: relative; display: inline-flex; align-items: center; justify-content: center; gap: 0.125rem; min-width: 2rem; height: 2rem; padding: 0 0.375rem; border-radius: 0.5rem; color: var(--mcm-button); transition: background-color 75ms, color 75ms; }
        .mcm-btn:hover:not(:disabled) { background: var(--mcm-hover); }
        .mcm-btn:disabled { opacity: 0.35; cursor: default; }
        .mcm-btn.mcm-on { background: var(--mcm-on-background); color: var(--mcm-on); }
        .mcm .mcm-btn svg { width: 1.125rem; height: 1.125rem; }
        .mcm .mcm-btn svg.mcm-caret { width: 0.75rem; height: 0.75rem; opacity: 0.6; }
        .mcm-color-bar { position: absolute; left: 0.375rem; right: 1.125rem; bottom: 0.25rem; height: 0.1875rem; border-radius: 1px; box-shadow: 0 0 0 1px rgba(127, 127, 127, 0.4); }
        .mcm-dropdown { position: relative; }
        .mcm-popover { position: absolute; top: calc(100% + 0.5rem); left: 0; z-index: 30; padding: 0.5rem; border-radius: 0.5rem; background: var(--mcm-popover); box-shadow: 0 0 0 1px var(--mcm-divider), 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1); }
        .mcm-default { display: flex; align-items: center; gap: 0.5rem; width: 100%; padding: 0.25rem 0.375rem; margin-bottom: 0.375rem; border-radius: 0.375rem; color: var(--mcm-button); font-size: 0.875rem; white-space: nowrap; }
        .mcm-default:hover { background: var(--mcm-hover); }
        .mcm-swatches { display: grid; grid-template-columns: repeat(8, 1.5rem); gap: 0.25rem; }
        .mcm-swatch { width: 1.5rem; height: 1.5rem; border-radius: 0.25rem; border: 1px solid rgba(127, 127, 127, 0.5); }
        .mcm-swatch:hover, .mcm-swatch.mcm-on { outline: 2px solid var(--mcm-focus); outline-offset: 1px; }
        .mcm-symbols-panel { max-height: 20rem; overflow-y: auto; }
        .mcm-symbols-title { margin: 0.375rem 0 0.25rem; color: var(--mcm-button); font-size: 0.75rem; font-weight: 600; opacity: 0.7; }
        .mcm-symbols-title:first-child { margin-top: 0; }
        .mcm-symbols { display: grid; grid-template-columns: repeat(10, 1.875rem); gap: 0.125rem; }
        .mcm-symbol { width: 1.875rem; height: 1.875rem; border-radius: 0.25rem; background: #2b2119; color: #fff; font-family: var(--mcm-font); font-variant-emoji: text; font-size: 1rem; line-height: 1; }
        .mcm-symbol:hover { background: #4a3a2c; outline: 2px solid var(--mcm-focus); }
        .mcm-hint { margin-left: auto; padding: 0 0.5rem; font-size: 0.875rem; color: var(--mcm-hint); }
        .mcm-screen { padding: 0.75rem; background-color: #2b2119; background-image: linear-gradient(45deg, rgba(0, 0, 0, 0.18) 25%, transparent 25%, transparent 75%, rgba(0, 0, 0, 0.18) 75%), linear-gradient(45deg, rgba(0, 0, 0, 0.18) 25%, transparent 25%, transparent 75%, rgba(0, 0, 0, 0.18) 75%); background-size: 8px 8px; background-position: 0 0, 4px 4px; overflow-x: auto; }
        .mcm-screen:last-child { border-radius: 0 0 0.5rem 0.5rem; }
        .mcm-entry { display: flex; gap: 0.5rem; max-width: 42rem; padding: 0.25rem; border: 2px solid #808080; background: rgba(0, 0, 0, 0.55); font-family: var(--mcm-font); font-variant-emoji: text; font-size: 1rem; line-height: 1.375rem; }
        .mcm-icon { flex: none; display: flex; align-items: center; justify-content: center; width: 4.125rem; height: 4.125rem; background: #3f3f3f; color: #8b8b8b; }
        .mcm .mcm-icon svg { width: 2.25rem; height: 2.25rem; }
        .mcm-body { flex: 1; min-width: 0; }
        .mcm-head { display: flex; align-items: center; gap: 0.75rem; color: #fff; white-space: nowrap; }
        .mcm-name { overflow: hidden; text-overflow: ellipsis; }
        .mcm-players { margin-left: auto; color: #aaaaaa; }
        .mcm-players span { color: #555555; }
        .mcm-ping { display: flex; align-items: flex-end; gap: 1px; height: 0.875rem; }
        .mcm-ping i { width: 0.1875rem; background: #00ff21; box-shadow: 1px 1px 0 #004a0b; }
        .mcm-editor { min-height: 2.75rem; outline: none; white-space: pre-wrap; overflow-wrap: anywhere; color: var(--mcm-text); caret-color: #fff; cursor: text; }
        .mcm-editor[contenteditable="false"] { cursor: default; }
        .mcm-editor div { min-height: 1.375rem; }
        .mcm-editor ::selection { background: rgba(80, 120, 255, 0.55); }
        .mcm-obfuscated { animation: mcm-obfuscated 0.3s steps(2) infinite; text-shadow: 0 0 4px currentColor; }
        @keyframes mcm-obfuscated { 0% { opacity: 1; filter: blur(0.5px); } 100% { opacity: 0.55; filter: blur(2px); } }
        .mcm-source { display: block; width: 100%; padding: 0.5rem 0.75rem; border: 0; border-bottom: 1px solid var(--mcm-divider); background: transparent; color: inherit; font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; font-size: 0.875rem; resize: vertical; }
        .mcm-source:focus { outline: none; box-shadow: none; }
    </style>

    <div
        class="mcm"
        style="--mcm-text: {{ MotdCodes::DEFAULT_COLOR }};"
        x-data="mcMotdEditor({
            state: $wire.$entangle(@js($getStatePath())),
            disabled: @js($isDisabled),
            colors: @js(MotdCodes::COLORS),
            defaultColor: @js(MotdCodes::DEFAULT_COLOR),
        })"
    >
        @unless ($isDisabled)
            <div class="mcm-toolbar" role="toolbar" aria-label="{{ $t('toolbar.label') }}">
                <div class="mcm-group">
                    <button type="button" class="mcm-btn" title="{{ $t('toolbar.undo') }} (Ctrl+Z)" x-on:mousedown.prevent x-on:click="undo()" x-bind:disabled="!canUndo">
                        <x-filament::icon icon="tabler-arrow-back-up" />
                    </button>
                    <button type="button" class="mcm-btn" title="{{ $t('toolbar.redo') }} (Ctrl+Y)" x-on:mousedown.prevent x-on:click="redo()" x-bind:disabled="!canRedo">
                        <x-filament::icon icon="tabler-arrow-forward-up" />
                    </button>
                </div>

                <div class="mcm-group">
                    @foreach ([
                        'l' => ['tabler-bold', 'Ctrl+B'],
                        'o' => ['tabler-italic', 'Ctrl+I'],
                        'n' => ['tabler-underline', 'Ctrl+U'],
                        'm' => ['tabler-strikethrough', null],
                        'k' => ['tabler-blur', null],
                    ] as $code => [$icon, $shortcut])
                        <button
                            type="button"
                            class="mcm-btn"
                            title="{{ $t('codes.' . $code) }} (§{{ $code }}){{ $shortcut ? ' – ' . $shortcut : '' }}"
                            x-on:mousedown.prevent
                            x-on:click="toggle('{{ $code }}')"
                            x-bind:class="{ 'mcm-on': active.{{ $code }} }"
                            x-bind:aria-pressed="active.{{ $code }}"
                        >
                            <x-filament::icon :icon="$icon" />
                        </button>
                    @endforeach
                </div>

                <div class="mcm-group">
                    <div class="mcm-dropdown" x-on:click.outside="colorsOpen = false" x-on:keydown.escape="colorsOpen = false">
                        <button type="button" class="mcm-btn" title="{{ $t('toolbar.color') }}" x-on:mousedown.prevent x-on:click="colorsOpen = !colorsOpen; symbolsOpen = false" x-bind:aria-expanded="colorsOpen">
                            <x-filament::icon icon="tabler-palette" />
                            <x-filament::icon icon="tabler-chevron-down" class="mcm-caret" />
                            <span
                                class="mcm-color-bar"
                                x-bind:style="mixedColor ? 'background: linear-gradient(90deg, #FF5555, #FFFF55, #55FF55, #55FFFF, #5555FF, #FF55FF)' : 'background: ' + (colors[active.c] ?? defaultColor)"
                            ></span>
                        </button>

                        <div class="mcm-popover" x-show="colorsOpen" x-cloak x-transition.opacity>
                            <button type="button" class="mcm-default" x-on:mousedown.prevent x-on:click="setColor(null)">
                                <span class="mcm-swatch" style="background: {{ MotdCodes::DEFAULT_COLOR }};"></span>
                                {{ $t('toolbar.default_color') }}
                            </button>
                            <div class="mcm-swatches">
                                @foreach (MotdCodes::COLORS as $code => $color)
                                    <button
                                        type="button"
                                        class="mcm-swatch"
                                        style="background: {{ $color }};"
                                        title="{{ $t('codes.' . $code) }} (§{{ $code }})"
                                        aria-label="{{ $t('codes.' . $code) }}"
                                        x-on:mousedown.prevent
                                        x-on:click="setColor(@js((string) $code))"
                                        x-bind:class="{ 'mcm-on': !mixedColor && active.c === @js((string) $code) }"
                                    ></button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="mcm-dropdown" x-on:click.outside="symbolsOpen = false" x-on:keydown.escape="symbolsOpen = false">
                        <button type="button" class="mcm-btn" title="{{ $t('toolbar.symbols') }}" x-on:mousedown.prevent x-on:click="symbolsOpen = !symbolsOpen; colorsOpen = false" x-bind:aria-expanded="symbolsOpen">
                            <x-filament::icon icon="tabler-omega" />
                            <x-filament::icon icon="tabler-chevron-down" class="mcm-caret" />
                        </button>

                        <div class="mcm-popover mcm-symbols-panel" x-show="symbolsOpen" x-cloak x-transition.opacity>
                            @foreach (MotdCodes::SYMBOLS as $group => $symbols)
                                <div class="mcm-symbols-title">{{ $t('symbols.' . $group) }}</div>
                                <div class="mcm-symbols">
                                    @foreach ($symbols as $symbol)
                                        {{-- The text variation selector keeps browsers from drawing a color emoji, it is only part of the label. --}}
                                        <button type="button" class="mcm-symbol" title="{{ $symbol }}" x-on:mousedown.prevent x-on:click="insertSymbol(@js($symbol))">{{ $symbol . "\u{FE0E}" }}</button>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="mcm-group">
                    <button type="button" class="mcm-btn" title="{{ $t('codes.r') }} (§r)" x-on:mousedown.prevent x-on:click="clearFormat()">
                        <x-filament::icon icon="tabler-clear-formatting" />
                    </button>
                    <button type="button" class="mcm-btn" title="{{ $t('toolbar.source') }}" x-on:click="toggleSource()" x-bind:class="{ 'mcm-on': showSource }" x-bind:aria-pressed="showSource">
                        <x-filament::icon icon="tabler-code" />
                    </button>
                </div>

                <span class="mcm-hint" x-show="limitHint" x-cloak x-transition.opacity>{{ $t('line_limit') }}</span>
            </div>

            <textarea
                class="mcm-source"
                rows="2"
                spellcheck="false"
                x-ref="source"
                x-show="showSource"
                x-cloak
                wire:ignore
                aria-label="{{ $t('toolbar.source') }}"
                x-on:input="onSourceInput($event.target.value)"
            ></textarea>
        @else
            <textarea x-ref="source" hidden wire:ignore></textarea>
        @endunless

        <div class="mcm-screen" @if ($isDisabled) style="border-radius: 0.5rem;" @endif>
            <div class="mcm-entry">
                <div class="mcm-icon">
                    <x-filament::icon icon="tabler-server-2" />
                </div>
                <div class="mcm-body">
                    <div class="mcm-head">
                        <span class="mcm-name">{{ $serverName }}</span>
                        @if ($maxPlayers !== null)
                            <span class="mcm-players">0<span>/</span>{{ $maxPlayers }}</span>
                        @endif
                        <span class="mcm-ping" aria-hidden="true" @if ($maxPlayers === null) style="margin-left: auto;" @endif>
                            <i style="height: 20%"></i><i style="height: 40%"></i><i style="height: 60%"></i><i style="height: 80%"></i><i style="height: 100%"></i>
                        </span>
                    </div>
                    <div
                        id="{{ $getId() }}"
                        class="mcm-editor"
                        role="textbox"
                        aria-multiline="true"
                        aria-label="{{ $getLabel() }}"
                        spellcheck="false"
                        contenteditable="{{ $isDisabled ? 'false' : 'true' }}"
                        wire:ignore
                        x-ref="editor"
                        x-on:beforeinput="onBeforeInput($event)"
                        x-on:input="onInput()"
                        x-on:keydown="onKeydown($event)"
                        x-on:compositionstart="onCompositionStart()"
                        x-on:compositionend="onCompositionEnd()"
                        x-on:copy="onCopy($event)"
                        x-on:cut="onCopy($event, true)"
                        x-on:paste="onPaste($event)"
                        x-on:dragstart.prevent
                        x-on:drop.prevent
                    ></div>
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
