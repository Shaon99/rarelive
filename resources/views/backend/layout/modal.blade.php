   <!-- Delete Modal-->
   <div class="modal fade" tabindex="-1" id="delete" role="dialog">
       <div class="modal-dialog" role="document">
           <form action="" method="post">
               @csrf
               {{ method_field('DELETE') }}
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">{{ __('Confirm Deletion') }} <span clase="mx-1" id="heading"></span>
                       </h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>

                   <div class="modal-body">
                       <p>{{ __('Are You Sure to Delete This Item') }}?
                           {{ __('Once Delete Data Move to Recycle Bin') }}</p>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary"
                           data-dismiss="modal">{{ __('Safe Data') }}</button>
                       <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                   </div>
               </div>
           </form>
       </div>
   </div>

   <div class="modal fade" tabindex="-1" id="deleteforever" role="dialog">
       <div class="modal-dialog" role="document">
           <form action="" method="post">
               @csrf
               {{ method_field('DELETE') }}
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">{{ __('Confirm Deletion') }}</h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
                   <div class="modal-body">
                       <p class="text-capitalize"> {{ __('Are You Sure to Delete This Item') }}?
                           {{ __('Once Delete Data Can Not Be Retrieve.') }}</p>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary"
                           data-dismiss="modal">{{ __('Safe Data') }}</button>

                       <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
                   </div>
               </div>
           </form>
       </div>
   </div>

   <div class="modal fade" tabindex="-1" id="deleteLiveWire" role="dialog">
       <div class="modal-dialog" role="document">
           <form action="" method="post">
               @csrf
               {{ method_field('DELETE') }}
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">{{ __('Confirm Deletion') }} <span clase="mx-1"
                               id="headingLiveWire"></span> ?
                       </h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
                   <div class="modal-body">
                       <p>{{ __('Are You Sure to Delete This Item') }}?
                           {{ __('Once Delete Data Move to Recycle Bin') }}</p>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary"
                           data-dismiss="modal">{{ __('Safe Data') }}</button>
                       <button type="button" class="btn btn-danger" id="confirmDeleteBtn">{{ __('Delete') }}</button>
                   </div>
               </div>
           </form>
       </div>
   </div>

   <!-- Create Modal-->
   <div class="modal fade" tabindex="-1" id="create" role="dialog">
       <div class="modal-dialog" role="document">
           <form action="" method="post" id="createFrom">
               @csrf
               {{ method_field('POST') }}
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">{{ __('Create') }} <span class="name"></span></h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
                   <div class="modal-body">
                       <label for="">{{ __('Enter Name') }}</label>
                       <input type="text" class="form-control" name="name" id="name" placeholder="Enter Name"
                           required="">
                       <div class="invalid-feedback">
                           {{ __('name can not be empty') }}
                       </div>
                       <small id="error-container" class="text-danger"></small>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary closeButton"
                           data-dismiss="modal">{{ __('Close') }}</button>

                       <button type="submit" class="btn btn-primary btnLoad">{{ __('Submit') }}</button>
                   </div>
               </div>
           </form>
       </div>
   </div>

   <!-- Edit Modal-->
   <div class="modal fade" tabindex="-1" id="edit" role="dialog">
       <div class="modal-dialog" role="document">
           <form action="" method="post">
               @csrf
               {{ method_field('PUT') }}
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">{{ __('Edit') }} <span class="name"></span></h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
                   <div class="modal-body">
                       <label for="">{{ __('Enter Name') }}</label>
                       <input type="text" class="form-control" name="name" placeholder="Enter Name">
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary"
                           data-dismiss="modal">{{ __('Close') }}</button>

                       <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                   </div>
               </div>
           </form>
       </div>
   </div>

   <!-- Edit Modal-->
   <div class="modal fade" tabindex="-1" id="due" role="dialog">
       <div class="modal-dialog" role="document">
           <form action="" method="post" class="needs-validation" novalidate="">
               @csrf
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title"><span class="name"></span></h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
                   <div class="modal-body">
                       <label for="">{{ __('Due Amount') }}</label>
                       <input type="text" readonly class="form-control due_amouunt mb-2" name="due_amount"
                           value="">
                       <label for="">{{ __('Payment Amount') }}</label>
                       <div class="input-group">
                           <input type="number" placeholder="Paying amount" class="form-control payment_amount"
                               name="payment_amount" value="" required="">

                           <div class="input-group-append">
                               <button type="button" class="btn btn-primary full-paid-btn">Full Paid</button>
                           </div>
                           <div class="invalid-feedback">
                               {{ __('payment amount can not be empty') }}
                           </div>
                       </div>
                       @php
                           $payment_methods = App\Models\PaymentMethod::select('id', 'type', 'name', 'account_number')
                               ->where('type', '!=', 'STEADFAST')
                               ->get()
                               ->values();
                       @endphp

                       <span class="error-due text-danger"></span>
                       <label for="tax" class="control-label mt-2">{{ __('Payment Method') }}</label>
                       <select class="form-control select2" name="payment_method" required=''>
                           <option value="" selected disabled>{{ __('Select Payment Method') }}</option>
                           @forelse ($payment_methods as $item)
                               <option value="{{ $item->id }}">{{ $item->name }} @if ($item->account_number)
                                       (AC: {{ $item->account_number }})
                                   @endif
                               </option>
                           @empty
                           @endforelse
                       </select>
                       <div class="invalid-feedback">
                           {{ __('please select a payment method') }}
                       </div>

                       <label for="note" class="control-label mt-2">{{ __('Note') }}</label>
                       <div class="input-group">
                           <textarea name="note" class="form-control" placeholder="Type here..."></textarea>
                       </div>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary"
                           data-dismiss="modal">{{ __('Close') }}</button>
                       <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                   </div>
               </div>
           </form>
       </div>
   </div>

   <!-- quantity transfer Modal-->
   <div class="modal fade" tabindex="-1" id="transfer" role="dialog">
       <div class="modal-dialog" role="document">
           <form action="" method="POST" class="needs-validation" novalidate="">
               @csrf
               <div class="modal-content">
                   <div class="modal-header">
                       <h5 class="modal-title">{{ __('Product ') }} <span class="name"></span></h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
                   <div class="modal-body">
                       <div class="form-group">
                           <label for="">{{ __('Sales Quantity') }}</label>
                           <input type="text" readonly class="form-control" name="sales_quantity" value="">
                       </div>
                       <div class="form-group">
                           <label for="">{{ __('Available Stock Quantity') }}</label>
                           <input type="text" readonly class="form-control stock_quantity" name="stock_quantity"
                               value="">
                       </div>

                       <div class="form-group">
                           <label for="">{{ __('Transfer quantity') }}</label>
                           <input type="number" placeholder="how much quantity want to transfer"
                               class="form-control transfer_quantity" name="transfer_quantity" value=""
                               required="">
                           <div class="invalid-feedback">
                               {{ __('quantity can not be empty') }}
                           </div>
                           <span class="error-quantity text-danger"></span>
                       </div>

                       <div class="form-group">
                           <label for="">{{ __('Current purchases price') }}</label>
                           <input type="text" readonly class="form-control current_purchases"
                               name="current_purchases" value="">
                       </div>


                       <div class="form-group">
                           <label for="">{{ __('Current sale price') }}</label>
                           <input type="text" readonly class="form-control current_saleprice"
                               name="current_saleprice" value="">
                       </div>


                       <div class="form-group">
                           <label for="">{{ __('Update Sale price') }}</label>
                           <input type="text" class="form-control update_saleprice" name="update_saleprice"
                               value="" placeholder="updated sale price">
                       </div>

                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary"
                           data-dismiss="modal">{{ __('Close') }}</button>

                       <button type="submit" class="btn btn-primary">{{ __('Submit') }}</button>
                   </div>
               </div>
           </form>
       </div>
   </div>

   <!-- Search Modal -->
   <div class="modal fade" tabindex="-1" id="searchModal" role="dialog">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-body">
                   <div class="search-input-container position-relative my-2">
                       <i class="fas fa-search search-icon"></i>
                       <input type="text" id="searchInput" class="search-input" placeholder="Search here..." />
                   </div>
                   <div class="search-suggestions mt-2">
                       @include('backend.layout.search_item')
                   </div>
               </div>
           </div>
       </div>
   </div>

   <!-- Modal -->
   <div class="modal fade" id="dataModal" tabindex="-1" aria-hidden="true">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="dataModalLabel">Main Data</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body">
                   <pre id="modalData"></pre>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
               </div>
           </div>
       </div>
   </div>

   <!-- Confirm Multiple Delete Modal -->
   <div class="modal fade" id="confirmDeleteMultipleModal" tabindex="-1" role="dialog"
       aria-labelledby="confirmDeleteLabel" aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="confirmDeleteLabel">Confirm Deletion</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body">
                   Are you sure you want to delete the selected files? This action cannot be undone.
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                   <button type="button" id="confirmMultipleDelete" class="btn btn-danger">Delete</button>
               </div>
           </div>
       </div>
   </div>

   <!-- Bootstrap Modal -->
   <div class="modal fade" id="confirmStatusModal" tabindex="-1" aria-labelledby="confirmStatusModalLabel"
       aria-hidden="true">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="confirmStatusModalLabel">Confirm Status Change</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body">
                   Are you sure you want to change the status to <strong id="newStatusText"></strong>?
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Close</button>
                   <button type="button" class="btn btn-primary" id="confirmStatusChange">Yes, Change</button>
               </div>
           </div>
       </div>
   </div>

   <!-- Bootstrap Modal Outside Livewire Component -->
   <div class="modal fade" id="selectedConfirmDeleteModal" tabindex="-1" role="dialog"
       aria-labelledby="selectedConfirmDeleteModal" aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="selectedConfirmDeleteModal">Confirm Deletion</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body">
                   Are you sure you want to delete the selected items?
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                   <button type="button" class="btn btn-danger" id="selectedDeleteButton">Delete</button>
               </div>
           </div>
       </div>
   </div>
   <!-- Hidden Form for POST request -->
   <form id="deleteForm" action="{{ route('admin.sales.deleteBulk') }}" method="POST" style="display:none;">
       @csrf
       @method('POST') <!-- Using POST method -->
       <input type="hidden" name="sales" id="salesIds">
   </form>

   <!-- Daily Report -->
   <div class="modal fade" id="daily-report" tabindex="-1" aria-hidden="true">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title f-12" id="title-daily-report">Daily Report Summary</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body" id="daily_report">
                   <!-- Report will be displayed here -->
                   <div id="loading-overlay load" class="loading-overlay">
                       <div class="loading-overlay-text text-center">please wait...</div>
                   </div>
               </div>
           </div>
       </div>
   </div>


   <!-- Edit Modal -->
   <div class="modal fade" id="editAddressModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel"
       aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="editModalLabel">{{ __('Edit Address') }}</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <form id="editAddressForm" method="POST">
                   @csrf
                   @method('PUT')
                   <div class="modal-body">
                       <div class="form-group">
                           <label>{{ __('City') }}</label>
                           <select class="form-control select2" name="city" id="citySelectModal" required>
                               <option value="" selected disabled>{{ __('Select City') }}</option>
                           </select>
                           <div class="invalid-feedback">
                               {{ __('City cannot be empty') }}
                           </div>
                       </div>

                       <div class="form-group">
                           <label>{{ __('Thana') }}</label>
                           <select class="form-control select2" name="thana" id="thanaSelectModal" required
                               disabled>
                               <option value="" selected disabled>{{ __('Select Thana') }}</option>
                           </select>
                           <div class="invalid-feedback">
                               {{ __('Thana cannot be empty') }}
                           </div>
                       </div>

                       <div class="form-group">
                           <label for="address">{{ __('Address') }}</label>
                           <textarea class="form-control" id="editAddress" name="address" required></textarea>
                       </div>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary"
                           data-dismiss="modal">{{ __('Close') }}</button>
                       <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                   </div>
               </form>
           </div>
       </div>
   </div>


   <div class="modal fade" id="trackingModal" tabindex="-1" aria-labelledby="trackingModalLabel"
       aria-hidden="true">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="trackingModalLabel">Courier Tracking Status</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>

               <div class="modal-body py-0">
                   <label for="">{{ __('Enter Steadfast Consignment ID') }}</label>
                   <input type="text" class="form-control" name="cid" id="cid"
                       placeholder="Enter consignment id">
                   <small class="error-show text-danger text-center"></small>
                   <div id="trackingResult" class="mt-3"></div>
               </div>
               <div class="modal-footer">
                   <button type="submit" class="btn btn-success"
                       id="checkStatusBtn">{{ __('Check Status') }}</button>
                   <button type="button" class="btn btn-secondary"
                       data-dismiss="modal">{{ __('Close') }}</button>
               </div>
           </div>
       </div>
   </div>


   <div class="modal fade" id="recent-order" tabindex="-1" role="dialog" aria-labelledby="recentSalesModalLabel"
       aria-hidden="true">
       <div class="modal-dialog modal-lg" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="recentSalesModalLabel">Recent 10 Sales</h5>
                   <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                       <span aria-hidden="true">&times;</span>
                   </button>
               </div>
               <div class="modal-body" id="view-recent-sales">
                   <div id="loading-overlay load-order" class="loading-overlay">
                       <div class="loading-overlay-text text-center">please wait...</div>
                   </div>

               </div>
           </div>
       </div>
   </div>


   <div class="modal fade" id="importModal" tabindex="-1" role="dialog" aria-labelledby="importModalLabel"
       aria-hidden="true">
       <div class="modal-dialog" role="document">
           <div class="modal-content">
               <form id="importForm" action="{{ route('admin.product.import') }}" method="POST"
                   enctype="multipart/form-data" class="needs-validation" novalidate="">
                   @csrf
                   <div class="modal-header">
                       <h5 class="modal-title" id="importModalLabel">{{ __('Import Products') }}</h5>
                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                           <span aria-hidden="true">&times;</span>
                       </button>
                   </div>
                   <div class="modal-body p-4">
                       <div class="upload-area mb-4">
                           <div class="text-center mb-3">
                               <i class="bi bi-file-earmark-arrow-up fs-1 text-primary"></i>
                               <h5 class="mt-2">Upload CSV File</h5>
                           </div>

                           <div
                               class="upload-container p-4 border border-2 border-dashed rounded-3 text-center position-relative">
                               <input type="file"
                                   class="file-input position-absolute d-none opacity-0 top-0 start-0 w-100 h-100"
                                   id="csvFile" name="csv_file" accept=".csv" required="">
                               <div class="invalid-feedback">
                                   {{ __('Csv file required') }}
                               </div>
                               <div class="upload-content">
                                   <p class="mb-2">Drag and drop your CSV file here</p>
                                   <p class="mb-0">or</p>
                                   <button type="button" class="btn btn-primary mt-2 px-4"
                                       onclick="document.getElementById('csvFile').click()">
                                       Browse Files
                                   </button>
                                   <p class="selected-file-name mt-2 mb-0 text-muted small" id="selectedFileName">No
                                       file selected</p>
                               </div>
                           </div>

                           <div class="text-center mt-3">
                               <small class="text-muted d-block">
                                   <i class="bi bi-info-circle me-1"></i> Supported format: .csv files only
                               </small>
                               <a href="sample-products.csv" download
                                   class="btn btn-link btn-sm mt-2 text-decoration-none">
                                   <i class="bi bi-download me-1"></i> Download Sample CSV
                               </a>
                           </div>
                       </div>
                   </div>
                   <div class="modal-footer">
                       <button type="button" class="btn btn-secondary"
                           data-dismiss="modal">{{ __('Close') }}</button>
                       <button type="submit" id="importButton" class="btn btn-primary">{{ __('Import') }}</button>
                   </div>
               </form>
           </div>
       </div>
   </div>


   <!-- Modal -->
   <div class="modal fade" id="galleryModal" tabindex="-1" role="dialog" data-backdrop="static"
       data-keyboard="false">
       <div class="modal-dialog modal-xl" role="document">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title">Select Image from Gallery</h5>
                   <button type="button" class="close" data-dismiss="modal">&times;</button>
               </div>
               <div class="modal-body">
                   <div class="container mb-4">
                       <div
                           class="upload-container p-4 border border-2 border-dashed rounded-3 text-center position-relative">
                           <input type="file" id="uploadImages" name="images[]" class="form-control d-none"
                               multiple>
                           <button type="button" class="btn btn-primary px-4" id="browseBtn">
                               <i class="fas fa-cloud-upload-alt me-2"></i> Browse Files
                           </button>
                           <p class="selected-file-name mt-2 mb-0 text-muted small" id="imageFileInfo">No images
                               selected</p>
                       </div>
                       <div id="upload-progress-area" class="mt-4"></div>
                   </div>

                   <div class="d-flex justify-content-between align-items-center mb-3">
                       <div class="d-flex align-items-center">
                           <div class="custom-control-g custom-checkbox modern-checkbox">
                               <input type="checkbox" id="selectAllImages" class="custom-control-input">
                               <label class="custom-control-label" for="selectAllImages">
                                   <span class="checkbox-text">Select All</span>
                               </label>
                           </div>
                       </div>
                       <button id="deleteSelectedBtn" class="btn btn-danger d-none">
                           <span class="spinner-border spinner-border-sm me-2 d-none" role="status"
                               aria-hidden="true"></span>
                           <span class="btn-text"><i class="fas fa-trash me-1"></i> Delete Selected</span>
                       </button>
                   </div>

                   <div id="gallery-images" class="row py-2" style="max-height: 500px; overflow-y: auto;">

                   </div>
                   <!-- Load More Button -->
                   <div class="text-center my-3" id="load-more-wrapper" style="display: none;">
                       <button id="load-more-images" class="btn btn-sm btn-primary">
                           Load More
                       </button>
                   </div>
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary"
                       data-dismiss="modal">{{ __('Close') }}</button>
               </div>
           </div>
       </div>
   </div>


   <!-- Confirm Delete Modal -->
   <div class="modal fade" id="confirmDeleteModalCloudinary" tabindex="-1">
       <div class="modal-dialog">
           <div class="modal-content">
               <div class="modal-header">
                   <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                   <button type="button" class="close" data-dismiss="modal">&times;</button>
               </div>
               <div class="modal-body">
                   Are you sure you want to delete <span id="selected-count"></span> selected image(s)?
               </div>
               <div class="modal-footer">
                   <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                   <button type="button" id="confirmDeleteBtnCloudinary" class="btn btn-danger">
                       <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                       <span class="btn-text">Delete</span>
                   </button>
               </div>
           </div>
       </div>
   </div>
