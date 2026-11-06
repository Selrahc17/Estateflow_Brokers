export default ({ role = 'client', step = 1 } = {}) => ({
    role,
    step,
    submitting: false,

    next() {
        const fields = [...this.$refs.accountStep.querySelectorAll('input')];

        if (!fields.every((field) => field.reportValidity())) {
            return;
        }

        this.step = 2;
        this.showStep(this.$refs.credentialsStep);
    },

    back() {
        this.step = 1;
        this.showStep(this.$refs.accountStep);
    },

    showStep(container) {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        this.$nextTick(() => container.querySelector('input')?.focus({ preventScroll: true }));
    },

    // Enter would otherwise try to submit fields on the hidden second step.
    continueOnEnter(event) {
        if (this.role === 'broker' && this.step === 1 && event.target.matches('input')) {
            event.preventDefault();
            this.next();
        }
    },
});
