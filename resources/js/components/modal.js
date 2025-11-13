// Modal Alpine.js component
document.addEventListener('alpine:init', () => {
    Alpine.data('confirmModal', () => ({
        show: false,
        title: '',
        message: '',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        confirmCallback: null,
        isDanger: false,

        open(options) {
            this.title = options.title || 'Confirm Action';
            this.message = options.message || 'Are you sure?';
            this.confirmText = options.confirmText || 'Confirm';
            this.cancelText = options.cancelText || 'Cancel';
            this.isDanger = options.isDanger || false;
            this.confirmCallback = options.onConfirm || null;
            this.show = true;
        },

        confirm() {
            if (this.confirmCallback) {
                this.confirmCallback();
            }
            this.close();
        },

        close() {
            this.show = false;
            this.confirmCallback = null;
        }
    }));

    Alpine.data('promptModal', () => ({
        show: false,
        title: '',
        message: '',
        placeholder: '',
        inputValue: '',
        confirmText: 'Submit',
        cancelText: 'Cancel',
        confirmCallback: null,
        inputType: 'text',

        open(options) {
            this.title = options.title || 'Enter Value';
            this.message = options.message || '';
            this.placeholder = options.placeholder || '';
            this.inputValue = options.defaultValue || '';
            this.confirmText = options.confirmText || 'Submit';
            this.cancelText = options.cancelText || 'Cancel';
            this.inputType = options.inputType || 'text';
            this.confirmCallback = options.onConfirm || null;
            this.show = true;

            // Focus input after modal opens
            this.$nextTick(() => {
                const input = this.$refs.promptInput;
                if (input) {
                    input.focus();
                    input.select();
                }
            });
        },

        confirm() {
            if (!this.inputValue.trim()) {
                window.showErrorToast('Please enter a value');
                return;
            }

            if (this.confirmCallback) {
                this.confirmCallback(this.inputValue);
            }
            this.close();
        },

        close() {
            this.show = false;
            this.inputValue = '';
            this.confirmCallback = null;
        }
    }));
});

// Global helper functions for modals
window.confirmAction = function(options) {
    const modal = Alpine.$data(document.querySelector('[x-data*="confirmModal"]'));
    if (modal) {
        modal.open(options);
    }
};

window.promptUser = function(options) {
    const modal = Alpine.$data(document.querySelector('[x-data*="promptModal"]'));
    if (modal) {
        modal.open(options);
    }
};

