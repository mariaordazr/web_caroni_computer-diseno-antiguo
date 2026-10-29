<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/pages/general/update" enctype="multipart/form-data">
                    <div class="admin-page-layout">
                        <div class="admin-form-column">
                            <div class="form-grid">
                                <div class="form-column">
                                    <h3>Logo del sitio</h3>
                                    <div class="logo_info">
                                        <div class="image-manager">
                                            <div class="image-slots-container" style="justify-content:center;">
                                                <?php 
                                                    $logoName = $logoHeader['content_value'] ?? null; 
                                                    $hasLogo = !empty($logoName);
                                                ?>
                                                <div class="image-slot <?php echo $hasLogo ? 'has-preview' : ''; ?>">
                                                    <input type="hidden" name="current_logo_name" value="<?php echo $hasLogo ? htmlspecialchars($logoName) : ''; ?>">
                                                    <input type="hidden" name="delete_logo" value=""> 
                                                    
                                                    <label for="logo-input">
                                                        <?php if ($hasLogo): ?>
                                                            <div class="image-preview-slot-frame">
                                                                <img src="/assets/uploads/logo/<?php echo htmlspecialchars($logoName); ?>" class="image-preview-slot" style="object-fit: contain;">
                                                                <button type="button" class="remove-preview-btn"><i class='bx bx-x'></i></button> 
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="image-placeholder-slot">
                                                                <i class='bx bx-image-add'></i>
                                                                <span>Subir Logo</span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </label>
                                                    <input type="file" name="image_logo" id="logo-input" accept="image/*" style="display:none;">
                                                </div>
                                            </div>
                                        </div>
                                        <div>
                                            <label for="money_symbol" class="form-label">Tipo de moneda</label>
                                            <input type="text" name="money_symbol" id="money_symbol" class="form-control" value="<?php echo htmlspecialchars($moneySymbol ?? ''); ?>" placeholder="Ej: S/">

                                            <label for="copyright" class="form-label">Texto Copyright (Footer)</label>
                                            <input type="text" name="copyright" id="copyright" class="form-control" value="<?php echo htmlspecialchars($copyrightText ?? ''); ?>" placeholder="Ej: CORPORACION GRUPONET SAC">
                                        </div>
                                    </div>

                                    <h3>Contacto Directo</h3>
                                
                                    <label for="phone" class="form-label">Número de Celular / WhatsApp</label>
                                    <input type="text" name="contact_number" id="phone" class="form-control" value="<?php echo htmlspecialchars($contactNumber ?? ''); ?>" placeholder="Ej: 51961146060">

                                    <label for="address" class="form-label">Dirección Física</label>
                                    <input type="text"  name="location_address" id="address" class="form-control" rows="3" value="<?php echo htmlspecialchars($locationAddress ?? ''); ?>">
                                </div>

                                <div class="form-column">
                                    <h3>Redes Sociales</h3>
                                    <p class="text-muted">Deja en blanco para ocultar el icono.</p>

                                    <label for="fb_link" class="form-label"><i class='bx bxl-facebook-square'></i> Facebook URL</label>
                                    <input type="text" name="facebook_link" id="fb_link" class="form-control" value="<?php echo htmlspecialchars($facebook ?? ''); ?>">

                                    <label for="ig_link" class="form-label"><i class='bx bxl-instagram-alt'></i> Instagram URL</label>
                                    <input type="text" name="instagram_link" id="ig_link" class="form-control" value="<?php echo htmlspecialchars($instagram ?? ''); ?>">

                                    <label for="yt_link" class="form-label"><i class='bx bxl-youtube'></i> YouTube URL</label>
                                    <input type="text" name="youtube_link" id="yt_link" class="form-control" value="<?php echo htmlspecialchars($youtube ?? ''); ?>">

                                    <label for="tt_link" class="form-label"><i class='bx bxl-tiktok'></i> TikTok URL</label>
                                    <input type="text" name="tiktok_link" id="tt_link" class="form-control" value="<?php echo htmlspecialchars($tiktok ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="admin-form-column">
                            <div class="admin-form-group">
                                <h3 style="margin-top: 2rem;">Métodos de Pago (Logos Footer)</h3>
                                        
                                 <div class="image-manager">
                                    <div class="image-slots-container" id="payments-container" style="flex-wrap: wrap;">
                                        <?php $currentIndex = 0; 
                                        if (!empty($paymentLogos)): 
                                            foreach ($paymentLogos as $pay):
                                                $imageName = $pay['content_value'];?>
                                                <div class="image-slot has-preview" data-index="<?php echo $currentIndex; ?>">
                                                    <input type="hidden" class="slot-image-id" name="slot_image_id_<?php echo $currentIndex; ?>" value="<?php echo htmlspecialchars($imageName); ?>">
                                                    <input type="hidden" class="delete-slot-flag" name="delete_image_<?php echo $currentIndex; ?>" value="">
                                                
                                                    <label for="image-slot-<?php echo $currentIndex; ?>">
                                                        <div class="image-preview-slot-frame">
                                                            <img src="/assets/uploads/payments/<?php echo htmlspecialchars($imageName); ?>" class="image-preview-slot">
                                                            <button type="button" class="remove-preview-btn" data-slot-index="<?php echo $currentIndex; ?>"><i class='bx bx-x'></i></button> 
                                                        </div>
                                                    </label>
                                                    <input type="file" name="image_slot_<?php echo $currentIndex; ?>" id="image-slot-<?php echo $currentIndex; ?>" accept="image/*" style="display:none;">
                                                </div>
                                                <?php $currentIndex++;
                                            endforeach; 
                                        endif; ?>
                                        <div class="image-slot" id="btn-add-payment">
                                            <label>
                                                <div class="image-placeholder-slot" >
                                                    <i class='bx bx-plus'></i>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>