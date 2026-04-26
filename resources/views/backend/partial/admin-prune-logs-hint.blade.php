<form method="post" action="{{ route('admin.maintenance.prune-old-logs') }}"
    class="d-flex flex-column align-items-stretch gap-3 p-4 bg-white border rounded-4 shadow"
    style="max-width:380px"
    onsubmit="return confirm(@json(__('This permanently deletes notifications and activity log rows older than the number of days you enter. Continue?')));">
    @csrf
    <div class="d-flex align-items-center mb-2">
        <span class="me-2" style="font-size:1.8rem; color: #f59f00;">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </span>
        <div>
            <div class="fw-semibold text-dark lh-sm">
                {{ __('Prune Old Notifications & Logs') }}
            </div>
            <div class="text-muted small">
                {{ __('Delete notifications and log entries older than the days you specify below.') }}
            </div>
        </div>
    </div>
    <div>
        <label for="prune-old-logs-days" class="form-label text-muted mb-1 small fw-medium">
            <i class="bi bi-calendar-x me-1"></i>
            {{ __('Delete entries older than (days)') }}
        </label>
        <input type="number" id="prune-old-logs-days" name="days"
            class="form-control form-control-lg rounded-3 border-2"
            style="max-width: 130px;"
            value="3" min="0" max="3650" required autocomplete="off">
        <div class="form-text small mt-1">
            <i class="bi bi-terminal me-1"></i>
            {{ __('Or use') }} <code>--days=N</code> {{ __('when running from CLI.') }}
        </div>
    </div>
    <button type="submit" class="btn btn-danger btn-lg rounded-3 d-flex align-items-center justify-content-center w-100 gap-2 shadow-sm">
        <x-heroicon-o-trash class="mr-1 hero-icon"/>
   
        <span> {{ __('Run Cleanup Now') }}</span>
    </button>
</form>
