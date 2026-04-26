<table class="table">
    <thead>
        <tr>
            <th>#</th>
            <th>Event</th>
            <th>Log Message</th>
            <th>Caused By</th>
            <th>Date</th>
            <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse ($activities as $activity)
            <tr>
                <td>{{ $loop->iteration + ($activities->currentPage() - 1) * $activities->perPage() }}</td>
                <td>{{ Str::headline($activity->event) }}</td>
                <td>{{ $activity->description }}</td>
                <td>{{ $activity->causer ? $activity->causer->name : 'N/A' }}</td>
                <td>{{ $activity->created_at->format('d M, y H:i:s A') }}</td>
                <td>
                    <a href="{{ route('admin.activityLog.show', $activity->id) }}" class="btn btn-success btn-sm"
                        title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center">{{ __('No activity available') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
@if ($activities->hasPages())
    {{ $activities->links('backend.partial.paginate') }}
@endif
