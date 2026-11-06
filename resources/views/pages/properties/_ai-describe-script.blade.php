@push('scripts')
<script>
document.querySelector('[data-ai-describe]')?.addEventListener('click', async (event) => {
    const button = event.currentTarget;
    const form = button.closest('form');
    const target = form.querySelector('#property-description');
    const status = form.querySelector('[data-ai-status]');
    const label = button.textContent;

    // _method would turn this POST into the edit form's PUT.
    const { _method, ...details } = Object.fromEntries(new FormData(form));

    button.disabled = true;
    button.textContent = 'Generating...';
    status.textContent = '';

    try {
        const response = await fetch(@js(route('agent.ai.describe')), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()), 'Accept': 'application/json' },
            body: JSON.stringify(details),
        });
        const result = await response.json();

        if (result.description) {
            target.value = result.description;
        } else {
            status.textContent = 'AI is not available right now, so please write the description yourself.';
        }
    } catch {
        status.textContent = 'AI is not available right now, so please write the description yourself.';
    } finally {
        button.disabled = false;
        button.textContent = label;
    }
});
</script>
@endpush
