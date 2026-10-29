<?php include ROOT_PATH . 'app/views/admin/templates/filterbar.php'; ?>

<?php $isCurrentUserAdmin = $userInfo['is_super_admin'] == 1; ?>
<section class="content-panel product-view-container view-list">    
    <?php if (!empty($receipts)): ?>
        <div class="table-list-ml">
            <?php foreach($receipts as $receipt): ?>
                <div class="table-item-ml">
                    <a href="/admin/receipts/view/<?php echo $receipt['id_receipt']; ?>" target="_blank">
                        
                        <div class="table-item-ml__info" style="min-width: 200px;">
                            <p class="info__meta">
                                Creado: <?php echo date('d/m/Y H:i', strtotime($receipt['created_at'])); ?>
                                <?php if ($receipt['created_at'] !== $receipt['updated_at']): ?>
                                     | Editado: <?php echo date('d/m/Y H:i', strtotime($receipt['updated_at'])); ?>
                                <?php endif; ?>
                            </p>
                            <p class="info__title"><?php echo htmlspecialchars($receipt['receipt_code']); ?></p>
                            <p class="info__title" style="font-weight: bold; font-size: 0.95em;"><?php echo htmlspecialchars($receipt['client_name']); ?></p>
                            <?php if (!empty($receipt['technician_name'])): ?>
                                <p style="color: #0d6efd; font-size: 0.85em; margin-top: 2px;">(<?php echo htmlspecialchars($receipt['technician_name']); ?>)</p>
                            <?php endif; ?>
                            <?php if ($receipt['is_finished'] == 1): ?>
                                <span style="display: inline-block; padding: 2px 8px; background: #e8f5e9; color: #198754; font-size: 0.7rem; border-radius: 4px; border: 1px solid #c3e6cb; margin-top: 5px;"><i class='bx bx-check-double'></i> Entregado / Finalizado</span>
                            <?php endif; ?>
                        </div>
                        <div class="table-item-ml__contact" style="width: 250px;">
                            <span class="item__cu">
                                <div class="contact-email">
                                    <i class='bx bx-envelope'></i> <?php echo htmlspecialchars($receipt['client_email']); ?>
                                </div>
                                <div class="contact-phone">
                                    <i class='bx bxl-whatsapp'></i> <?php echo htmlspecialchars($receipt['client_phone']); ?>
                                </div>
                            </span>
                        </div>
                        <div class="table-item-ml__contact" style="width: 80px;">
                            <span class="item__cu">
                                <div class="status-item">
                                    <?php if ($receipt['sent_email']): ?>
                                        <span class="status-text status-text--sent">Enviado</span>
                                        <i class='bx bxs-check-circle status-text--sent'></i>
                                    <?php else: ?>
                                        <span class="status-text status-text--pending">No enviado</span>
                                        <i class='bx bx-minus-circle status-text--pending'></i>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="status-item status-item--whatsapp">
                                    <?php if ($receipt['sent_whatsapp']): ?>
                                        <span class="status-text status-text--sent">Enviado</span>
                                        <i class='bx bxs-check-circle status-text--sent'></i>
                                    <?php else: ?>
                                        <span class="status-text status-text--pending">No enviado</span>
                                        <i class='bx bx-minus-circle status-text--pending'></i>
                                    <?php endif; ?>
                                </div>
                            </span>
                        </div>
                    </a>
                    <div class="table-item-ml__actions">
                        <div class="actions-menu-container">
                            <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="actions-dropdown">
                                <a href="/admin/receipts/view/<?php echo $receipt['id_receipt']; ?>" target="_blank"><i class='bx bxs-file-pdf'></i> Ver PDF</a>
                                <a href="#" class="btn-private-info"
                                   data-tech="<?php echo htmlspecialchars($receipt['technician_name'] ?? ''); ?>" 
                                   data-notes="<?php echo htmlspecialchars($receipt['private_notes'] ?? ''); ?>">
                                    <i class='bx bx-lock-alt'></i> Ver Info Privada
                                </a>
                                <?php if ($receipt['is_finished'] == 0): ?>
                                    <a href="/admin/receipts/edit/<?php echo $receipt['id_receipt']; ?>"><i class='bx bx-edit'></i> Editar</a>
                                    <a href="#" onclick="toggleReceiptStatus(<?php echo $receipt['id_receipt']; ?>, 1); return false;"><i class='bx bx-check-double'></i> Finalizar Nota</a>
                                <?php else: ?>
                                    <a href="#" onclick="toggleReceiptStatus(<?php echo $receipt['id_receipt']; ?>, 0); return false;"><i class='bx bx-lock-open-alt'></i> Reabrir Nota</a>
                                <?php endif; ?>

                                <a href="/admin/receipts/view/<?php echo $receipt['id_receipt']; ?>?action=email" target="_blank"><i class='bx bx-envelope'></i> Enviar por Correo</a>
                                <a href="/admin/receipts/view/<?php echo $receipt['id_receipt']; ?>?action=whatsapp" target="_blank"><i class='bx bxl-whatsapp'></i> Enviar por WhatsApp</a>
                                
                                <?php if ($isCurrentUserAdmin): ?>
                                    <a href="#" class="danger" onclick="deleteReceipt(<?php echo $receipt['id_receipt']; ?>); return false;">
                                        <i class='bx bx-trash'></i> Eliminar
                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="generic-grid-container">
            <?php foreach($receipts as $receipt): ?>
                <div class="grid-item-card">
                    <a href="/admin/receipts/view/<?php echo $receipt['id_receipt']; ?>" target="_blank">
                        <div class="grid-item-card__image">
                            <picture>
                                <i class='bx bx-file' style="font-size: 7em;"></i>
                            </picture>
                        </div>
                    </a>
                    <a href="/admin/receipts/view/<?php echo $receipt['id_receipt']; ?>" target="_blank">
                        <div class="grid-item-card__content">
                             <p class="info__meta">
                                <?php echo date('d/m/Y H:i', strtotime($receipt['created_at'])); ?>
                                <?php if ($receipt['created_at'] !== $receipt['updated_at']): ?>
                                     (Editado)
                                <?php endif; ?>
                            </p>
                            <p class="info__title"><?php echo htmlspecialchars($receipt['receipt_code']); ?></p>
                            <p class="info__title" style="font-weight: bold; font-size: 0.95em;"><?php echo htmlspecialchars($receipt['client_name']); ?></p>
                            <?php if (!empty($receipt['technician_name'])): ?>
                                <p style="color: #0d6efd; font-size: 0.85em; margin-top: 2px;">(<?php echo htmlspecialchars($receipt['technician_name']); ?>)</p>
                            <?php endif; ?>
                            <?php if ($receipt['is_finished'] == 1): ?>
                                <span style="display: inline-block; padding: 2px 8px; background: #e8f5e9; color: #198754; font-size: 0.7rem; border-radius: 4px; border: 1px solid #c3e6cb; margin-top: 5px;"><i class='bx bx-check-double'></i> Entregado / Finalizado</span>
                            <?php endif; ?>
                            <span class="item__cu" style="padding-top: 10px;">
                                <div class="status-item">
                                    <?php if ($receipt['sent_email']): ?>
                                        <i class='bx bx-envelope'></i><span class="status-text status-text--sent">: Enviado</span>
                                        <i class='bx bxs-check-circle status-text--sent'></i>
                                    <?php else: ?>
                                        <i class='bx bx-envelope'></i><span class="status-text status-text--pending">: No enviado</span>
                                        <i class='bx bx-minus-circle status-text--pending'></i>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="status-item status-item--whatsapp">
                                    <?php if ($receipt['sent_whatsapp']): ?>
                                        <i class='bx bxl-whatsapp'></i><span class="status-text status-text--sent">: Enviado</span>
                                        <i class='bx bxs-check-circle status-text--sent'></i>
                                    <?php else: ?>
                                        <i class='bx bxl-whatsapp'></i><span class="status-text status-text--pending">: No enviado</span>
                                        <i class='bx bx-minus-circle status-text--pending'></i>
                                    <?php endif; ?>
                                </div>
                            </span>
                        </div>

                    </a>
                    <div class="grid-item-card__actions">
                        <div class="actions-menu-container">
                            <button class="actions-menu-toggle"><i class='bx bx-dots-vertical-rounded'></i></button>
                            <div class="actions-dropdown">
                                <a href="/admin/receipts/view/<?php echo $receipt['id_receipt']; ?>" target="_blank"><i class='bx bxs-file-pdf'></i> Ver PDF</a>
                                <a href="#" class="btn-private-info"
                                   data-tech="<?php echo htmlspecialchars($receipt['technician_name'] ?? ''); ?>" 
                                   data-notes="<?php echo htmlspecialchars($receipt['private_notes'] ?? ''); ?>">
                                    <i class='bx bx-lock-alt'></i> Ver Info Privada
                                </a>
                                <?php if ($receipt['is_finished'] == 0): ?>
                                    <a href="/admin/receipts/edit/<?php echo $receipt['id_receipt']; ?>"><i class='bx bx-edit'></i> Editar</a>
                                    <a href="#" onclick="toggleReceiptStatus(<?php echo $receipt['id_receipt']; ?>, 1); return false;"><i class='bx bx-check-double'></i> Finalizar Nota</a>
                                <?php else: ?>
                                    <a href="#" onclick="toggleReceiptStatus(<?php echo $receipt['id_receipt']; ?>, 0); return false;"><i class='bx bx-lock-open-alt'></i> Reabrir Nota</a>
                                <?php endif; ?>

                                <a href="/admin/receipts/view/<?php echo $receipt['id_receipt']; ?>?action=email" target="_blank"><i class='bx bx-envelope'></i> Enviar por Correo</a>
                                <a href="/admin/receipts/view/<?php echo $receipt['id_receipt']; ?>?action=whatsapp" target="_blank"><i class='bx bxl-whatsapp'></i> Enviar por WhatsApp</a>
                                
                                <?php if ($isCurrentUserAdmin): ?>
                                    <a href="#" class="danger" onclick="deleteReceipt(<?php echo $receipt['id_receipt']; ?>); return false;">
                                        <i class='bx bx-trash'></i> Eliminar
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-results-message">
            <i class='bx bx-receipt'></i>
            <h2>No hay notas generadas</h2>
            <p>Presiona el botón "Agregar" para crear la primera nota de entrega.</p>
        </div>
    <?php endif; ?>
</section>

<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>