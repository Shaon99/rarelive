<table class="table">
    <thead>
        <tr>
            <th>
                <input type="checkbox" id="selectAll" title="Select All">
            </th>
            <th>#</th>
            <th>Title</th>
            <th>Deleted Data</th>
            <th class="text-center">View Data</th>
            <th class="text-center">Deleted At</th>
            <th class="text-right">Actions</th>
        </tr>
    </thead>
    <tbody>
        <div id="loading-overlay" class="loading-overlay" style="display: none;">
            <div class="loading-overlay-text text-center">please wait...</div>
        </div>
        @forelse($recycleBins as $item)
            <tr>
                <td>
                    <input type="checkbox" name="ids[]" value="{{ $item['id'] }}" class="fileCheckbox">
                </td>
                <td>{{ $loop->iteration + ($recycleBins->currentPage() - 1) * $recycleBins->perPage() }}</td>
                <td>{{ class_basename($item->model) }}</td>
                @php
                    $data = json_decode($item->data, true);
                    unset($data['id'], $data['created_at'], $data['updated_at'], $data['deleted_at']);
                    $filteredData = json_encode($data);
                @endphp

                <td>
                    {{ Str::limit(implode(', ', collect($data)->flatten()->toArray()), 20, '...') }}
                </td>

                <td class="text-center">
                    <button type="button" class="btn btn-success btn-sm viewData" title="View Data" data-json="{{ $filteredData }}">
                        <i class="fa fa-eye"></i>
                    </button>
                </td>

                <td class="text-center">{{ \Carbon\Carbon::parse($item->deleted_at)->format('d M, Y H:i:s A') }}</td>
                <td class="text-right">
                    <form action="{{ route('admin.recycle_bin.restore', $item->id) }}" method="POST"
                        style="display: inline;">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm" title="Restore">
                            <i class="fas fa-trash-restore"></i>
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger btn-sm ml-2 deleteforever"
                        data-href="{{ route('admin.recycle_bin.delete_forever', $item->id) }}" title="Delete Forever">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="100%" class="text-center">{{ __('No activity available') }}</td>
            </tr>
        @endforelse
    </tbody>
</table>
@if ($recycleBins->hasPages())
    {{ $recycleBins->links('backend.partial.paginate') }}
@endif
