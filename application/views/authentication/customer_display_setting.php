<?php
$base_color = '';

if (!function_exists('get_embed_info')) {
    function get_embed_info($url, $default_type = 'image') {
        if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|hosts/[^/]+/|watch\?v=|r/|shorts/)|youtu\.be/)([^"&?/\s]{11})%i', $url, $match)) {
            $video_id = $match[1];
            return [
                'type' => 'youtube',
                'url' => "https://www.youtube.com/embed/{$video_id}?autoplay=0&mute=1&loop=0&controls=1"
            ];
        }
        
        if (stripos($url, 'facebook.com') !== false || stripos($url, 'fb.watch') !== false || stripos($url, 'fb.gg') !== false) {
            $encoded_url = urlencode($url);
            return [
                'type' => 'facebook',
                'url' => "https://www.facebook.com/plugins/video.php?href={$encoded_url}&autoplay=false&mute=true&show_text=false&t=0"
            ];
        }

        $ext = strtolower(pathinfo($url, PATHINFO_EXTENSION));
        if (in_array($ext, ['mp4', 'webm', 'ogg'])) {
            return [
                'type' => 'video',
                'url' => $url
            ];
        } elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            return [
                'type' => 'image',
                'url' => $url
            ];
        }

        return [
            'type' => $default_type,
            'url' => $url
        ];
    }
}
?>
<!-- Main content -->
<section class="main-content-wrapper">
    <!-- Success/Error Notifications -->
    <?php if ($this->session->flashdata('exception')) { ?>
        <section class="alert-wrapper">
            <div class="alert alert-success alert-dismissible fade show"> 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="alert-body">
                    <p><i class="m-right fa fa-check"></i><?= escape_output($this->session->flashdata('exception')); unset($_SESSION['exception']); ?></p>
                </div>
            </div>
        </section>
    <?php } ?>
    
    <?php if ($this->session->flashdata('exception_er')) { ?>
        <section class="alert-wrapper">
            <div class="alert alert-danger alert-dismissible fade show"> 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                <div class="alert-body">
                    <p><i class="m-right fa fa-times"></i><?= escape_output($this->session->flashdata('exception_er')); unset($_SESSION['exception_er']); ?></p>
                </div>
            </div>
        </section>
    <?php } ?>

    <section class="content-header">
        <h3 class="top-left-header">
            Customer Display Slideshow Settings
        </h3>
    </section>

    <!-- Upload Panel & Preview Grid -->
    <div class="box-wrapper">
        <div class="row">
            <!-- Left panel: File Uploader -->
            <div class="col-md-4">
                <div class="table-box" style="padding: 25px; border-radius: 12px; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <h4 style="margin-bottom: 20px; font-weight: 600; color: #333;">Upload New Media</h4>
                    
                    <?= form_open_multipart(base_url('setting/customer_display_upload'), ['id' => 'slideshow_upload_form']); ?>
                        <div class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 500; margin-bottom: 8px; color: #555;">Media Source Type</label>
                            <select id="media_source_type" name="media_source_type" class="form-control" style="border-radius: 6px; margin-bottom: 15px; height: 40px; border-color: #cbd5e1;">
                                <option value="local">Upload Local File</option>
                                <option value="external">Add External URL (Facebook, Imgur, CDN, etc.)</option>
                            </select>
                        </div>

                        <!-- Local File Upload Container -->
                        <div id="local_upload_container" class="form-group" style="margin-bottom: 20px;">
                            <label style="font-weight: 500; margin-bottom: 8px; color: #555;">Choose Image or Video</label>
                            
                            <!-- Custom drag-and-drop container -->
                            <div id="drop-zone" style="border: 2px dashed #ccc; border-radius: 8px; padding: 30px 15px; text-align: center; background: #fafafa; cursor: pointer; transition: all 0.3s;">
                                <i class="fa fa-cloud-upload" style="font-size: 36px; color: #888; margin-bottom: 10px;"></i>
                                <p style="margin: 0; font-size: 14px; color: #666; font-weight: 500;">Drag & Drop files here</p>
                                <p style="margin: 5px 0 0; font-size: 12px; color: #999;">or click to browse from computer</p>
                                <input type="file" id="slideshow_file" name="slideshow_file" accept="image/*,video/*" style="display: none;" required />
                            </div>
                            
                            <!-- Selected file preview panel -->
                            <div id="file-info-preview" style="display: none; margin-top: 15px; padding: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 6px;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div id="file-thumbnail" style="width: 45px; height: 45px; border-radius: 4px; overflow: hidden; background: #e2e8f0; display: flex; align-items: center; justify-content: center;">
                                        <i class="fa fa-file" style="color: #64748b; font-size: 20px;"></i>
                                    </div>
                                    <div style="flex: 1; min-width: 0;">
                                        <p id="file-name" style="margin: 0; font-size: 13px; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"></p>
                                        <p id="file-size" style="margin: 2px 0 0; font-size: 11px; color: #64748b;"></p>
                                    </div>
                                    <button type="button" id="remove-selected-file" style="background: none; border: none; color: #ef4444; cursor: pointer; padding: 5px;"><i class="fa fa-times-circle" style="font-size: 18px;"></i></button>
                                </div>
                            </div>
                        </div>

                        <!-- External URL Container -->
                        <div id="external_url_container" style="display: none; margin-bottom: 20px;">
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="font-weight: 500; margin-bottom: 8px; color: #555;">External Media URL</label>
                                <input type="url" id="external_url" name="external_url" class="form-control" placeholder="https://example.com/image.jpg" style="border-radius: 6px; height: 40px; border-color: #cbd5e1;" />
                                <small style="display: block; margin-top: 5px; color: #64748b;">
                                    Note: Paste a direct image/video URL (e.g. from Facebook, Imgur, or your own host).
                                </small>
                            </div>
                            <div class="form-group" style="margin-bottom: 15px;">
                                <label style="font-weight: 500; margin-bottom: 8px; color: #555;">Media Type</label>
                                <select name="external_type" class="form-control" style="border-radius: 6px; height: 40px; border-color: #cbd5e1;">
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                </select>
                            </div>
                        </div>

                        <div style="font-size: 12px; color: #666; line-height: 1.5; margin-bottom: 25px; background: #fef3c7; border-left: 4px solid #f59e0b; padding: 10px; border-radius: 4px;">
                            <strong>Supported Formats & Info:</strong><br/>
                            - Images: JPG, JPEG, PNG, GIF<br/>
                            - Videos: MP4, WEBM (Loopable)<br/>
                            - Max File Size (Local): 15 MB<br/>
                            - External URLs must be direct, publicly accessible links.
                        </div>

                        <button type="submit" name="submit" value="submit" id="submit_btn" class="btn btn-success w-100" style="padding: 10px; font-weight: 600; font-size: 14px; border-radius: 6px; background-color: #f97316; border-color: #f97316;">Upload Media</button>
                    <?= form_close(); ?>
                </div>
            </div>

            <!-- Right panel: Preview Grid of uploaded files -->
            <div class="col-md-8">
                <div class="table-box" style="padding: 25px; border-radius: 12px; background: #fff; box-shadow: 0 4px 15px rgba(0,0,0,0.05); min-height: 400px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                        <h4 style="font-weight: 600; color: #333; margin: 0;">Currently Uploaded Slides (<?= count($slides) ?>)</h4>
                        <a href="<?= base_url('Sale/customer_display') ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="border-radius: 6px; font-weight: 500; color: #f97316; border-color: #f97316;"><i class="fa fa-external-link" style="margin-right: 5px;"></i> View Customer Screen</a>
                    </div>
                    
                    <?php if (empty($slides)) { ?>
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 300px; color: #94a3b8; text-align: center;">
                            <i class="fa fa-picture-o" style="font-size: 64px; margin-bottom: 15px; color: #cbd5e1;"></i>
                            <h5 style="font-weight: 600; color: #64748b; margin-bottom: 5px;">No custom slides uploaded</h5>
                            <p style="font-size: 13px; max-width: 320px;">Currently displaying the default fallback food images. Upload your own slides on the left to customize the screen!</p>
                        </div>
                    <?php } else { ?>
                        <div class="row" style="row-gap: 20px;">
                            <?php foreach ($slides as $slide) { 
                                $embed = get_embed_info($slide['url'], $slide['type']);
                            ?>
                                <div class="col-md-4 col-sm-6">
                                    <div style="border: 1px solid #e2e8f0; border-radius: 10px; overflow: hidden; background: #fafafa; display: flex; flex-direction: column; height: 100%; transition: all 0.3s; box-shadow: 0 2px 8px rgba(0,0,0,0.02);" class="slide-preview-card">
                                        <!-- Visual Container -->
                                        <div style="position: relative; width: 100%; padding-top: 56.25%; background: #000; overflow: hidden; border-bottom: 1px solid #e2e8f0;">
                                            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center;">
                                                <?php if ($embed['type'] === 'youtube' || $embed['type'] === 'facebook') { ?>
                                                    <iframe src="<?= $embed['url'] ?>" frameborder="0" allowfullscreen style="width: 100%; height: 100%; border: none;"></iframe>
                                                <?php } elseif ($embed['type'] === 'video') { ?>
                                                    <video src="<?= $embed['url'] ?>" controls muted style="width: 100%; height: 100%; object-fit: cover;"></video>
                                                <?php } else { ?>
                                                    <img src="<?= $embed['url'] ?>" alt="Slide" style="width: 100%; height: 100%; object-fit: cover;" />
                                                <?php } ?>
                                            </div>
                                            <!-- Media Type Badge -->
                                            <span style="position: absolute; top: 10px; right: 10px; background: rgba(0,0,0,0.7); color: #fff; padding: 2px 8px; border-radius: 4px; font-size: 10px; font-weight: 600; text-transform: uppercase;">
                                                <?= (isset($slide['is_external']) && $slide['is_external'] ? 'External ' : '') . $embed['type'] ?>
                                            </span>
                                        </div>
                                        
                                        <!-- Footer Info -->
                                        <div style="padding: 12px; display: flex; flex-direction: column; justify-content: space-between; flex: 1; min-height: 0;">
                                            <div style="margin-bottom: 10px;">
                                                <p style="margin: 0; font-size: 12px; font-weight: 600; color: #1e293b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?= escape_output($slide['name']) ?>">
                                                    <?= escape_output($slide['name']) ?>
                                                </p>
                                                <p style="margin: 2px 0 0; font-size: 11px; color: #64748b;">
                                                    <?php if (isset($slide['is_external']) && $slide['is_external']): ?>
                                                        External Link
                                                    <?php else: ?>
                                                        Size: <?= $slide['size'] ?> KB
                                                    <?php endif; ?>
                                                </p>
                                            </div>
                                            <a href="<?= base_url('setting/customer_display_delete/' . urlencode($slide['name'])) ?>" class="btn btn-sm btn-danger w-100 delete-slide-btn" style="border-radius: 6px; padding: 6px; font-weight: 600; font-size: 12px; display: flex; align-items: center; justify-content: center; gap: 5px; background: #ef4444; border-color: #ef4444;">
                                                <i class="fa fa-trash"></i> Delete Slide
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Inline CSS to style the cards & dropzone hovering -->
<style>
.slide-preview-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.06) !important;
    border-color: #cbd5e1 !important;
}
#drop-zone.dragover {
    border-color: #f97316 !important;
    background: #fff7ed !important;
    color: #ea580c !important;
}
</style>

<!-- Drag and Drop JS & File Previews -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const dropZone = document.getElementById('drop-zone');
    const fileInput = document.getElementById('slideshow_file');
    const fileInfoPreview = document.getElementById('file-info-preview');
    const fileName = document.getElementById('file-name');
    const fileSize = document.getElementById('file-size');
    const fileThumbnail = document.getElementById('file-thumbnail');
    const removeSelectedFile = document.getElementById('remove-selected-file');
    const uploadForm = document.getElementById('slideshow_upload_form');
 
    // Toggle Source Type (using jQuery to ensure compatibility with custom select plugins like Select2)
    $('#media_source_type').on('change', function() {
        if ($(this).val() === 'local') {
            $('#local_upload_container').show();
            $('#external_url_container').hide();
            $('#slideshow_file').attr('required', 'required');
            $('#external_url').removeAttr('required');
            $('#submit_btn').text('Upload Media');
        } else {
            $('#local_upload_container').hide();
            $('#external_url_container').show();
            $('#slideshow_file').removeAttr('required');
            $('#external_url').attr('required', 'required');
            $('#submit_btn').text('Add External URL');
        }
    });
    // Trigger on page load to initialize correct state
    $('#media_source_type').trigger('change');

    // Trigger click on file input when dropzone clicked
    dropZone.addEventListener('click', () => fileInput.click());

    // Handle Drag events
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('dragover');
        }, false);
    });

    // Handle dropped files
    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            fileInput.files = files;
            updateFilePreview(files[0]);
        }
    });

    // Handle selected files
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            updateFilePreview(fileInput.files[0]);
        }
    });

    // Remove selected file
    removeSelectedFile.addEventListener('click', (e) => {
        e.stopPropagation();
        fileInput.value = '';
        fileInfoPreview.style.display = 'none';
        dropZone.style.display = 'block';
    });

    // Generate preview
    function updateFilePreview(file) {
        fileName.textContent = file.name;
        
        // Format size
        let sizeStr = '';
        if (file.size > 1024 * 1024) {
            sizeStr = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
        } else {
            sizeStr = (file.size / 1024).toFixed(2) + ' KB';
        }
        fileSize.textContent = sizeStr;

        // Reset thumbnail
        fileThumbnail.innerHTML = '<i class="fa fa-file" style="color: #64748b; font-size: 20px;"></i>';

        // Check file type and display thumbnail if image
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                fileThumbnail.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;" />`;
            }
            reader.readAsDataURL(file);
        } else if (file.type.startsWith('video/')) {
            fileThumbnail.innerHTML = '<i class="fa fa-file-video-o" style="color: #f97316; font-size: 20px;"></i>';
        }

        // Switch panels
        dropZone.style.display = 'none';
        fileInfoPreview.style.display = 'block';
    }

    // Confirm deletion
    const deleteButtons = document.querySelectorAll('.delete-slide-btn');
    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this slide? It will be permanently removed from the Customer Display.')) {
                e.preventDefault();
            }
        });
    });
});
</script>
