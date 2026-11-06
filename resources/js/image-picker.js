const MAX_FILE_SIZE = 4 * 1024 * 1024;

const fileKey = (file) => `${file.name}-${file.size}-${file.lastModified}`;

/**
 * Alpine component for property photo inputs. A file input forgets its earlier
 * selection each time the picker opens, so the chosen files are kept here and
 * written back to the input, which is what the form submits.
 */
export default function imagePicker({ max = 10, existing = 0, replace = false } = {}) {
    return {
        max,
        existing,
        files: [],
        removed: [],
        error: '',

        get used() {
            return this.existing - this.removed.length + this.files.length;
        },

        add(event) {
            const chosen = [...event.target.files];
            const problems = [];

            if (replace) {
                this.clear();
            }

            const valid = chosen.filter((file) => {
                if (!file.type.startsWith('image/')) {
                    problems.push(`${file.name} is not an image.`);
                    return false;
                }

                if (file.size > MAX_FILE_SIZE) {
                    problems.push(`${file.name} is larger than 4 MB.`);
                    return false;
                }

                return true;
            });

            const selected = new Set(this.files.map(({ file }) => fileKey(file)));
            const fresh = valid.filter((file) => !selected.has(fileKey(file)));
            const slots = Math.max(0, this.max - this.used);

            if (fresh.length > slots) {
                const skipped = fresh.length - slots;
                problems.push(`The limit is ${this.max} photos, so ${skipped} ${skipped === 1 ? 'was' : 'were'} not added.`);
            }

            fresh.slice(0, slots).forEach((file) => {
                this.files.push({ file, url: URL.createObjectURL(file) });
            });

            this.error = problems.join(' ');
            this.sync();
        },

        remove(index) {
            URL.revokeObjectURL(this.files[index].url);
            this.files.splice(index, 1);
            this.error = '';
            this.sync();
        },

        clear() {
            this.files.forEach(({ url }) => URL.revokeObjectURL(url));
            this.files = [];
        },

        sync() {
            const transfer = new DataTransfer();
            this.files.forEach(({ file }) => transfer.items.add(file));
            this.$refs.input.files = transfer.files;
        },
    };
}
