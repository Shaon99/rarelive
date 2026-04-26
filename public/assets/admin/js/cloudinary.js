$(document).ready(function() {
    // Initialize variables
    const $uploadInput = $("#uploadImages");
    const $browseBtn = $("#browseBtn");
    const $imageFileInfo = $("#imageFileInfo");
    const $uploadProgressArea = $("#upload-progress-area");
    let nextCursor = null;
    let selectedUrls = [];
    let selectedElements = [];

    // File upload handling
    $browseBtn.on("click", function() {
        $uploadInput.click();
    });

    $uploadInput.on("change", function(event) {
        const files = event.target.files;
        if (!files.length) return;

        $imageFileInfo.text(`${files.length} image(s) selected`);
        $uploadProgressArea.empty();
        $browseBtn.prop("disabled", true).html('<i class="fas fa-cloud-upload-alt me-2"></i> Uploading...');

        let completedUploads = 0;

        Array.from(files).forEach((file, index) => {
            const formData = new FormData();
            formData.append("image", file);

            const progressId = "progress-" + index;
            const statusId = "status-" + index;
            const containerId = "progress-container-" + index;

            $uploadProgressArea.append(`
                <div id="${containerId}" class="mb-3">
                    <p class="mb-1 text-start">${file.name}</p>
                    <div class="progress" style="height: 20px;">
                        <div id="${progressId}" class="progress-bar bg-success" role="progressbar" style="width: 0%">0%</div>
                    </div>
                    <small id="${statusId}" class="text-muted">Starting upload...</small>
                </div>
            `);

            $.ajax({
                url: "/gallery-image-upload",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
                },
                data: formData,
                contentType: false,
                processData: false,
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(event) {
                        if (event.lengthComputable) {
                            const percent = Math.round(event.loaded / event.total * 95);
                            $(`#${progressId}`).css("width", `${percent}%`).text(`${percent}%`);
                        }
                    }, false);
                    return xhr;
                },
                success: function(response) {
                    $(`#${progressId}`).css("width", "100%").text("100%");
                    
                    setTimeout(() => {
                        $(`#${containerId}`).fadeOut(400, function() {
                            $(this).remove();
                        });

                        const imageUrl = response.url;
                        const imageHtml = `
                            <div class="col-md-3 col-sm-4 col-4 mb-3">
                                <div class="gallery-image position-relative" data-url="${imageUrl}">
                                    <input type="checkbox" class="select-image-checkbox position-absolute m-2" style="top: 8px; left: 8px; z-index: 11; width: 18px; height: 18px;">
                                    <img src="${imageUrl}" data-url="${imageUrl}" alt="Uploaded Image" class="img-thumbnail select-img w-100 rounded select-gallery-image cursor-pointer" style="object-fit: cover;">
                                </div>
                            </div>
                        `;

                        $(".no-record").length && $(".no-record").text("");
                        $("#gallery-images").prepend(imageHtml);
                    }, 500);
                },
                error: function() {
                    $(`#${progressId}`).removeClass("bg-success").addClass("bg-danger").text("Failed");
                    $(`#${statusId}`).text("Upload failed");
                    
                    setTimeout(() => {
                        $(`#${containerId}`).fadeOut(400, function() {
                            $(this).remove();
                        });
                    }, 3000);
                },
                complete: function() {
                    completedUploads++;
                    if (completedUploads === files.length) {
                        $browseBtn.prop("disabled", false).html('<i class="fas fa-cloud-upload-alt me-2"></i> Browse Files');
                    }
                }
            });
        });
    });

    // Load gallery images
    function loadGalleryImages() {
        $.ajax({
            url: "/gallery-image",
            type: "GET",
            data: nextCursor ? { next_cursor: nextCursor } : {},
            beforeSend: function() {
                if (nextCursor) {
                    $("#load-more-images").html('<i class="fas fa-spinner fa-spin"></i> Loading...').prop("disabled", true);
                } else {
                    $("#gallery-images").html('<div class="text-center col-md-12"><i class="fas fa-spinner fa-spin"></i> Loading images...</div>');
                }
            },
            success: function(response) {
                if (response.images && response.images.length > 0) {
                    if (!nextCursor) {
                        $("#gallery-images").empty();
                    }

                    response.images.forEach(imageUrl => {
                        const imageHtml = `
                            <div class="col-md-3 mb-3 col-sm-4 col-4 image-wrapper">
                                 <div class="gallery-image position-relative" data-url="${imageUrl}">
                                    <input type="checkbox" class="select-image-checkbox position-absolute m-2" style="top: 8px; left: 8px; z-index: 11; width: 18px; height: 18px;">
                                    <img src="${imageUrl}" data-url="${imageUrl}" alt="Uploaded Image" class="img-thumbnail select-img w-100 rounded select-gallery-image cursor-pointer" style="object-fit: cover;">
                                </div>
                            </div>
                        `;
                        $("#gallery-images").append(imageHtml);
                    });

                    nextCursor = response.next_cursor || null;

                    if (nextCursor) {
                        $("#load-more-wrapper").show();
                        $("#load-more-images").html("Load More").prop("disabled", false);
                    } else {
                        $("#load-more-wrapper").hide();
                    }
                } else {
                    if (!nextCursor) {
                        $("#gallery-images").html('<div class="text-center text-danger col-md-12 no-record">No images found.</div>');
                    }
                    $("#load-more-wrapper").hide();
                }
            },
            error: function(error) {
                console.error("Error fetching images:", error);
                $("#gallery-images").html('<div class="text-center text-danger">Failed to load images. Please try again.</div>');
                $("#load-more-wrapper").hide();
            }
        });
    }

    // Update hidden inputs
    function updateGalleryInputs(urls) {
        const $uploadArea = $("#uploaded_gallery_urls").parent(".upload-area");
        $('input[name="gallery_urls[]"]').remove();
        
        urls.forEach(url => {
            const encodedUrl = encodeURIComponent(url);
            $uploadArea.append(`<input type="hidden" name="gallery_urls[]" value="${encodedUrl}">`);
        });
    }

    // Update gallery preview
    function updateGalleryPreview(urls) {
        const $preview = $("#gallery-preview");
        $preview.empty();
        
        urls.forEach(url => {
            $preview.append(`
                <div class="mr-2 mb-3" style="width: 100px; height: 100px; position: relative;">
                    <div class="image-container position-relative w-100 h-100">
                        <img src="${url}" class="img-thumbnail w-100 h-100 rounded" style="object-fit: cover;">
                        <button type="button" class="btn btn-danger btn-sm remove-image position-absolute rounded-circle"
                                style="width: 22px; height: 22px; padding: 0; top: 2px; right: 2px; display: flex; align-items: center; justify-content: center; border: 1px solid white;"
                                data-url="${url}">
                            <input type="hidden" name="gallery_urls[]" value="${url}">
                            <i class="fas fa-times" style="font-size: 0.6rem;"></i>
                        </button>
                    </div>
                </div>
            `);
        });
    }

    // Toggle delete button
    function toggleDeleteButton() {
        const hasSelected = $(".select-image-checkbox:checked").length > 0;
        $("#deleteSelectedBtn").toggleClass("d-none", !hasSelected).prop("disabled", !hasSelected);
    }

    // Open gallery modal
    $(document).on("click", ".imageGallery", function(event) {
        event.preventDefault();
        const selectionType = $(this).data("selection-type");
        $("#galleryModal").data("selection-type", selectionType);
        $("#galleryModal").modal("show");
        $("#gallery-images").html('<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading images...</div>');
        $("#load-more-wrapper").hide();
        nextCursor = null;
        loadGalleryImages();
    });

    // Load more images
    $(document).on("click", "#load-more-images", function() {
        loadGalleryImages();
    });

    // Select gallery image
    $(document).on("click", ".select-gallery-image", function(event) {
        event.preventDefault();
        event.stopPropagation();

        const $this = $(this);
        const imageUrl = String($this.data("url"));
        const selectionType = $("#galleryModal").data("selection-type");
        const $galleryImage = $this.closest(".gallery-image").length ? $this.closest(".gallery-image") : $this;
        const $checkbox = $galleryImage.find(".select-image-checkbox");

        if (!imageUrl) {
            console.error("No URL found for image:", $this);
            return;
        }

        if (selectionType === "single") {
            $("#uploaded_image_url").val(imageUrl);
            $("#image-preview").css("background-image", `url(${imageUrl})`).addClass("img-append");
            $("#imageFileInfo").text("1 image selected");
            $("#galleryModal").modal("hide");
        } 
        else if (selectionType === "multiple") {
            let currentUrls = $("#galleryModal").data("selectedUrls") || [];
            const urlIndex = currentUrls.indexOf(imageUrl);

            if (urlIndex !== -1) {
                currentUrls.splice(urlIndex, 1);
                $galleryImage.removeClass("selected");
                $checkbox.prop("checked", false);
            } else {
                currentUrls.push(imageUrl);
                $galleryImage.addClass("selected");
                $checkbox.prop("checked", true);
            }

            $("#galleryModal").data("selectedUrls", currentUrls);
            updateGalleryInputs(currentUrls);
            updateGalleryPreview(currentUrls);
            $("#galleryFileInfo").text(`${currentUrls.length} image(s) selected`);
        }
    });

    // Remove image from preview
    $(document).on("click", ".remove-image", function() {
        const imageUrl = $(this).data("url");
        let currentUrls = $("#galleryModal").data("selectedUrls") || [];
        currentUrls = currentUrls.filter(url => url !== imageUrl);
        
        $("#galleryModal").data("selectedUrls", currentUrls);
        updateGalleryInputs(currentUrls);
        updateGalleryPreview(currentUrls);
        $("#galleryFileInfo").text(`${currentUrls.length} image(s) selected`);
        
        $("#galleryModal").find(`.select-gallery-image[data-url="${imageUrl}"]`)
            .closest(".gallery-image")
            .removeClass("selected")
            .find(".select-image-checkbox")
            .prop("checked", false);
    });

    // Initialize modal
    $("#galleryModal").on("show.bs.modal", function() {
        $(this).data("selectedUrls", []);
    });

    // Select all images
    $("#selectAllImages").on("change", function() {
        const isChecked = $(this).is(":checked");
        $(".select-image-checkbox").prop("checked", isChecked);
        toggleDeleteButton();
    });

    // Checkbox change handler
    $(document).on("change", ".select-image-checkbox", function() {
        toggleDeleteButton();
        const totalCheckboxes = $(".select-image-checkbox").length;
        const checkedCheckboxes = $(".select-image-checkbox:checked").length;
        $("#selectAllImages").prop("checked", totalCheckboxes === checkedCheckboxes);
    });

    // Delete selected images
    $("#deleteSelectedBtn").on("click", function() {
        selectedElements = $(".select-image-checkbox:checked").closest(".gallery-image");
        if (!selectedElements.length) return;

        selectedUrls = selectedElements.map(function() {
            return $(this).data("url");
        }).get();

        $("#selected-count").text(selectedUrls.length);
        $("#confirmDeleteModalCloudinary").modal("show");
    });

    // Confirm delete
    $("#confirmDeleteBtnCloudinary").on("click", function() {
        const $btn = $(this);
        $btn.prop("disabled", true);
        $btn.find(".spinner-border").removeClass("d-none");
        $btn.find(".btn-text").text("Deleting...");

        $.ajax({
            url: "/gallery-image-delete-bulk",
            method: "POST",
            data: {
                image_urls: selectedUrls,
                _token: $('meta[name="csrf-token"]').attr("content")
            },
            success: function(response) {
                if (response.success) {
                    selectedElements.closest(".image-wrapper").fadeOut(400, function() {
                        $(this).remove();
                    });
                    $("#deleteSelectedBtn").addClass("d-none");
                    showToast("Images deleted successfully", "success");
                } else {
                    showToast("Some images could not be deleted.", "error");
                }
            },
            error: function() {
                showToast("Failed to delete selected images.", "error");
            },
            complete: function() {
                $("#confirmDeleteModalCloudinary").modal("hide");
                $btn.prop("disabled", false);
                $btn.find(".spinner-border").addClass("d-none");
                $btn.find(".btn-text").html("Delete");
                $(".select-image-checkbox").prop("checked", false);
                $("#selectAllImages").prop("checked", false);
            }
        });
    });
});