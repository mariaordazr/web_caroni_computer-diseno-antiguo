<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/assets/uploads/logo/gruponet.webp">
    <title>Nota de Recibo - <?php echo htmlspecialchars($data['receipt_code']); ?></title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>

    <style>
        /* 1. Contenedor simulando una hoja física (Abraza el contenido) */
        #invoice-content {
            font-family: 'Inter', Arial, sans-serif !important;
            font-size: 12px !important;
            color: #333 !important;
            background: white !important;
            text-align: left;
            
            max-width: 800px;
            margin: 0 auto;
            padding: 40px !important;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            box-sizing: border-box;
            display: block !important; /* IMPORTANTE: Asegura que no se estire */
        }
        
        /* 2. Reseteo estricto de tablas */
        #invoice-content * { box-sizing: border-box; }
        #invoice-content table { width: 100% !important; border-collapse: collapse !important; margin-bottom: 0; border-spacing: 0; }
        
        /* 3. Cabecera */
        #invoice-content .header-table { margin-top: 55px; margin-bottom: 20px; border-bottom: 2px solid #333 !important; padding-bottom: 10px; }
        #invoice-content .header-table td { vertical-align: middle; border: none !important; padding: 0 !important; background: transparent !important; }
        #invoice-content .company-info { text-align: right; line-height: 1.4; }
        #invoice-content .company-name { font-size: 16px !important; font-weight: bold !important; color: #000 !important; margin-bottom: 5px; text-transform: uppercase;}
        
        /* 4. Datos del Cliente */
        #invoice-content .meta-table { margin-bottom: 20px; }
        #invoice-content .meta-table td { vertical-align: top; padding: 10px !important; border: 1px solid #ccc !important; background-color: #f9f9f9 !important; }
        #invoice-content .box-title { font-weight: bold; font-size: 13px !important; color: #000 !important; margin-bottom: 8px; border-bottom: 1px solid #ccc !important; padding-bottom: 4px; text-transform: uppercase; }
        
        /* 5. Tablas de Equipos y Presupuesto */
        #invoice-content .items-table { margin-bottom: 20px; }
        #invoice-content .items-table th { 
            background-color: #333 !important; 
            color: white !important; 
            font-weight: bold !important; 
            padding: 8px 5px !important; 
            text-align: left !important; 
            font-size: 10px !important; 
            text-transform: uppercase; 
            border: 1px solid #333 !important;
        }
        #invoice-content .items-table td { border: 1px solid #ccc !important; padding: 8px 5px !important; vertical-align: top; }
        
        /* 6. Observaciones y Footer */
        #invoice-content .notes-section { margin-top: 15px; font-size: 10px !important; line-height: 1.4;}
        #invoice-content .notes-label { font-weight: bold; color: #000; }
        #invoice-content .notes-block { margin-bottom: 8px; }
        
        #invoice-content .pdf-footer-clean { 
            margin-top: 30px !important; /* Margen fijo para pegarlo arriba */
            font-size: 9px !important; 
            text-align: center; 
            color: #777 !important; 
            border-top: 1px solid #ccc !important; 
            padding-top: 5px; 
        }

        /* --- AJUSTES AL IMPRIMIR EN FÍSICO / PDF DEL NAVEGADOR --- */
        @media print {
            @page {
                margin: 0; /* MAGIA: Esto apaga la Fecha, Hora y URL automáticas del navegador */
            }
            body { background: white !important; }
            .pdf-toolbar, .modal-tabs-container, #btn-open-gallery { display: none !important; }
            
            #invoice-content { 
                box-shadow: none !important; 
                padding: 1.5cm !important; /* Le devolvemos el margen interno para que no toque los bordes del papel */
                max-width: 100% !important; 
                width: 100% !important;
                display: block !important; 
            }
            #invoice-content .items-table th { background-color: #333 !important; color: white !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
            #invoice-content .meta-table td { background-color: #f9f9f9 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
    <link rel="stylesheet" href="/assets/admin/css/receipt-pdf.css">

</head>
<body class="pdf-viewer-body">
    <?php
        if (!function_exists('getImageUrl')) {
            function getImageUrl($img) {
                if (strpos($img, 'http') === 0) {
                    if (preg_match('/(d\/|id=)([a-zA-Z0-9_-]+)/', $img, $matches)) {
                        // Usamos la API de Thumbnail de Google para evitar errores 403 Forbidden
                        return 'https://drive.google.com/thumbnail?id=' . $matches[2] . '&sz=w1000';
                    }
                    return $img;
                }
                return '/assets/uploads/receipts/' . $img;
            }
        }
    ?>
    <?php 
        $receiptImages = json_decode($data['images_json'] ?? '[]', true) ?: []; 
        $processImages = json_decode($data['process_images_json'] ?? '[]', true) ?: []; 
    ?>
    <div class="pdf-toolbar">
        
        <button class="btn-tool btn-print" onclick="window.print()">
            <i class='bx bx-printer'></i> <span class="btn-text">Imprimir / Guardar PDF</span>
        </button>
        
        <?php if ((!empty($receiptImages)) || (!empty($processImages))): ?>
            <button class="btn-tool btn-gallery no-print" id="btn-open-gallery">
                <i class='bx bx-images'></i> <span class="btn-text">Ver Fotos</span>
            </button>
        <?php endif; ?>

        <?php if (!isset($isPublic)): ?>
            <button class="btn-tool btn-email" onclick="openEmailModal()">
                <i class='bx bx-envelope'></i> <span class="btn-text">Enviar por Correo</span>
            </button>
            <button class="btn-tool btn-wa" onclick="openWaModal()">
                <i class='bx bxl-whatsapp'></i> <span class="btn-text">Enviar por WhatsApp</span>
            </button>
        <?php endif; ?>
        
    </div>

    <div class="invoice-wrapper">
        <div class="pdf-container" id="invoice-content">
            <?php 
                // Estas dos variables se aseguran de extraer SIEMPRE los datos, pase lo que pase
                $itemsList = !empty($items) ? $items : (json_decode($data['items_json'] ?? '[]', true) ?: []);
                $budgetList = !empty($budget) ? $budget : (json_decode($data['budget_json'] ?? '[]', true) ?: []);
            ?>

            <table class="header-table">
                <tr>
                    <td style="width: 40%;">
                        <img src="/assets/uploads/logo/gruponet.webp" style="max-width: 180px; max-height: 70px;">
                    </td>
                    <td style="width: 60%;" class="company-info">
                        <div class="company-name"><?php echo htmlspecialchars($settings['company_name'] ?? 'GRUPO CARONI COMPUTER C.A'); ?></div>
                        RIF/ID: <?php echo htmlspecialchars($settings['company_id'] ?? ''); ?><br>
                        Teléfono: <?php echo htmlspecialchars($settings['company_phone_1'] ?? ''); ?> <?php echo !empty($settings['company_phone_2']) ? ' / ' . htmlspecialchars($settings['company_phone_2']) : ''; ?><br>
                        Email: <?php echo htmlspecialchars($settings['company_email'] ?? ''); ?><br>
                        Dirección: <?php echo htmlspecialchars($settings['company_address'] ?? ''); ?>
                    </td>
                </tr>
            </table>

            <table class="meta-table">
                <tr>
                    <td style="width: 60%; border-right: none;">
                        <div class="box-title">RECIBIMOS DE:</div>
                        <strong><?php echo htmlspecialchars($data['client_name']); ?></strong><br>
                        C.I./DNI: <?php echo htmlspecialchars($data['client_id_number']); ?><br>
                        Teléfono: <?php echo htmlspecialchars($data['client_phone']); ?><br>
                        Email: <?php echo htmlspecialchars($data['client_email']); ?><br>
                        Dirección: <?php echo htmlspecialchars($data['client_address']); ?>
                    </td>
                    <td style="width: 40%;">
                        <div class="box-title">NOTA DE RECIBO</div>
                        <strong>ID:</strong> <?php echo htmlspecialchars($data['receipt_code']); ?><br>
                        <strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($data['created_at'])); ?>
                    </td>
                </tr>
            </table>

            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">Und.</th>
                        <th style="width: 15%;">Modelo</th>
                        <th style="width: 15%;">S/N</th>
                        <th style="width: 20%;">Complementos</th>
                        <th style="width: 20%;">Detalles físicos</th>
                        <th style="width: 25%;">Características y fallas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($itemsList)): foreach($itemsList as $item): ?>
                    <tr>
                        <td style="text-align: center;"><?php echo htmlspecialchars($item['und'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['model'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['serial'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['accessories'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['details'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($item['features'] ?? ''); ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr>
                        <td colspan="6" style="text-align:center;">No hay equipos registrados</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php 
                $itemsList = !empty($items) ? $items : (json_decode($data['items_json'] ?? '[]', true) ?: []);
                
                $budgetRaw = !empty($budget) ? $budget : (json_decode($data['budget_json'] ?? '[]', true) ?: []);
                if (isset($budgetRaw['items'])) {
                    $budgetList = $budgetRaw['items'];
                    $finalAmountPdf = $budgetRaw['final_amount'] ?? '00.00';
                    $tentativeList = $budgetRaw['tentative_items'] ?? [];
                    $tentativeFinalAmountPdf = $budgetRaw['tentative_final_amount'] ?? '';
                } else {
                    $budgetList = $budgetRaw;
                    $finalAmountPdf = $data['final_amount'] ?? '00.00';
                    $tentativeList = [];
                    $tentativeFinalAmountPdf = '';
                }

                // VALIDACIÓN: Comprobar si hay datos reales en el definitivo
                $hasDefinitive = false;
                if (!empty($budgetList)) {
                    foreach($budgetList as $b) {
                        if (!empty(trim($b['description'])) || (isset($b['amount']) && trim($b['amount']) !== '0.00' && trim($b['amount']) !== '')) {
                            $hasDefinitive = true; break;
                        }
                    }
                }

                // VALIDACIÓN: Comprobar si hay datos reales en el tentativo
                $hasTentative = false;
                if (!empty($tentativeList)) {
                    foreach($tentativeList as $t) {
                        if (!empty(trim($t['description'])) || (isset($t['amount']) && trim($t['amount']) !== '0.00' && trim($t['amount']) !== '')) {
                            $hasTentative = true; break;
                        }
                    }
                }
            ?>

            <?php if ($hasDefinitive): ?>
                <!-- SE MUESTRA SOLO SI HAY PRESUPUESTO DEFINITIVO -->
                <div style="font-weight: bold; margin-bottom: 5px; font-size: 12px; color: #0d6efd;">PRESUPUESTO DEFINITIVO:</div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 65%; background-color: #0d6efd !important; border: 1px solid #0d6efd !important;">Descripción</th>
                            <th style="width: 35%; background-color: #0d6efd !important; border: 1px solid #0d6efd !important;">Monto (<?php echo htmlspecialchars($moneySymbol ?? ''); ?>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($budgetList as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['amount'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (!empty($finalAmountPdf) && $finalAmountPdf !== '00.00'): ?>
                    <div style="text-align: right; font-weight: bold; font-size: 14px; color: #000; margin-top: 8px; padding-right: 5px; margin-bottom: 20px;">
                        MONTO FINAL (DEFINITIVO): <?php echo htmlspecialchars($moneySymbol ?? ''); ?> <?php echo htmlspecialchars($finalAmountPdf); ?>
                    </div>
                <?php endif; ?>

            <?php elseif ($hasTentative): ?>
                <!-- SE MUESTRA SOLO SI NO HAY DEFINITIVO PERO SÍ HAY TENTATIVO -->
                <div style="font-weight: bold; font-size: 12px; color: #000;">PRESUPUESTO TENTATIVO:</div>
                <div style="font-size: 10px; color: #666; margin-bottom: 5px; font-style: italic;">* Este presupuesto inicial está sujeto a cambios</div>
                
                <table class="items-table">
                    <thead>
                        <tr>
                            <th style="width: 65%;">Descripción</th>
                            <th style="width: 35%;">Monto (<?php echo htmlspecialchars($moneySymbol ?? ''); ?>)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($tentativeList as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['description'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($item['amount'] ?? ''); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (!empty($tentativeFinalAmountPdf)): ?>
                    <div style="text-align: right; font-weight: bold; font-size: 14px; color: #000; margin-top: 8px; padding-right: 5px; margin-bottom: 20px;">
                        MONTO FINAL (TENTATIVO): <?php echo htmlspecialchars($moneySymbol ?? ''); ?> <?php echo htmlspecialchars($tentativeFinalAmountPdf); ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

          
            <?php if(!empty($data['notes'])): ?>
                <div class="notes-section">
                    <span class="notes-label">OBSERVACIONES:</span><br>
                    <?php echo nl2br(htmlspecialchars($data['notes'])); ?>
                </div>
            <?php endif; ?>

            <div class="notes-section" style="margin-top: 30px; border-top: 1px dashed #ccc; padding-top: 15px;">
                <?php if(!empty($settings['company_note'])): ?>
                    <div class="notes-block">
                        <span class="notes-label">IMPORTANTE:</span> 
                        <?php echo nl2br(htmlspecialchars($settings['company_note'])); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($settings['company_condition'])): ?>
                    <div class="notes-block">
                        <span class="notes-label">CONDICIÓN:</span> 
                        <?php echo nl2br(htmlspecialchars($settings['company_condition'])); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($settings['company_warranty'])): ?>
                    <div class="notes-block">
                        <span class="notes-label">GARANTÍA:</span> 
                        <?php echo nl2br(htmlspecialchars($settings['company_warranty'])); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="pdf-footer-clean">
                Este documento constituye un control de mercancía recibida. | Generado el <?php echo date('d/m/Y H:i', strtotime($data['created_at'])); ?>
            </div>
        </div>
    </div>    


    <div id="emailModal" class="email-modal-overlay" style="display: none;">
        <div class="email-modal">
            <div class="email-modal-header">
                <h3>Mensaje de Correo</h3>
                <button class="btn-close-modal" onclick="closeEmailModal()">&times;</button>
            </div>
            <div class="email-modal-body">
                <div class="form-group">
                    <label>Para:</label>
                    <input type="email" id="modal_to" value="<?php echo htmlspecialchars($data['client_email']); ?>">
                </div>
                <div class="form-group">
                    <label>Asunto:</label>
                    <input type="text" id="modal_subject" value="Nota de recibo de <?php echo htmlspecialchars($data['client_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Mensaje:</label>
                    <textarea id="modal_message" rows="5">Hola <?php echo htmlspecialchars($data['client_name']); ?>,

A través del siguiente enlace podrá visualizar y descargar su nota de recibo detallada.
Además podrá visualizar imagenes de cómo se recibió el equipo y del proceso de reparación.

Saludos cordiales,
<?php echo htmlspecialchars($settings['company_name'] ?? ''); ?>
                    </textarea>
                </div>
                <div class="attachment-pill">
                    🔗 Se incluirá un enlace seguro a la nota de recibo
                </div>
            </div> 
            <div class="email-modal-footer">
                <button class="btn-send-mail" id="modal_btn_send" onclick="sendCustomEmail()">Enviar Enlace</button>
            </div>
        </div> 
    </div>

    <div id="waModal" class="email-modal-overlay" style="display: none;">
        <div class="email-modal">
            <div class="email-modal-header">
                <h3>Mensaje de WhatsApp</h3>
                <button class="btn-close-modal" onclick="closeWaModal()">&times;</button>
            </div>
            <div class="email-modal-body">
                <div class="form-group">
                    <label>Número de WhatsApp:</label>
                    <input type="text" id="modal_wa_phone" value="<?php echo htmlspecialchars(preg_replace('/\D+/', '', $data['client_phone'])); ?>">
                </div>
                <div class="form-group">
                    <label>Mensaje a enviar:</label>
                    <textarea id="modal_wa_message" rows="7">Hola <?php echo htmlspecialchars($data['client_name']); ?>, somos del equipo de <?php echo htmlspecialchars($settings['company_name'] ?? 'Caroni Computer'); ?>.

Te contactamos para enviarte el enlace a tu nota de recibo
Aquí podrás ver los detalles de tu equipo, fotos del estado en que lo recibimos y del proceso de reparación:
https://<?php echo htmlspecialchars($_SERVER['HTTP_HOST']); ?>/nota/<?php echo htmlspecialchars($data['access_token']); ?>

Cualquier duda, estamos a la orden.
                    </textarea>
                </div>
                <div class="attachment-pill-wa">
                    🔗 El cliente podrá ver y descargar el PDF desde este enlace
                </div>
            </div> 
            <div class="email-modal-footer">
                <button class="btn-send-mail btn-send-wa" onclick="sendCustomWhatsApp()">Abrir WhatsApp</button>
            </div>
        </div> 
    </div>

    <?php         
        $initialImagesRaw = json_decode($data['images_json'] ?? '[]', true) ?: []; 
        $processImagesRaw = json_decode($data['process_images_json'] ?? '[]', true) ?: [];
        
        $initialMedia = [];
        foreach ($initialImagesRaw as $media) {
            if (strpos($media, '[VIDEO]') === 0) {
                $initialMedia[] = ['type' => 'video', 'src' => str_replace('[VIDEO]', '', $media)];
            } else {
                $initialMedia[] = ['type' => 'image', 'src' => $media];
            }
        }

        $processMedia = [];
        foreach ($processImagesRaw as $media) {
            if (strpos($media, '[VIDEO]') === 0) {
                $processMedia[] = ['type' => 'video', 'src' => str_replace('[VIDEO]', '', $media)];
            } else {
                $processMedia[] = ['type' => 'image', 'src' => $media];
            }
        }

        $hasInitial = count($initialMedia) > 0;
        $hasProcess = count($processMedia) > 0;
    ?>
    
    <?php if ($hasInitial || $hasProcess): ?>
        <div id="product-gallery-modal" class="product-gallery-modal no-print">
            <button class="modal-close-btn" id="receipt-modal-close">&times;</button>
            <button class="modal-prev-btn" id="receipt-modal-prev">&#10094;</button>
            <button class="modal-next-btn" id="receipt-modal-next">&#10095;</button>
            
            <?php if ($hasInitial && $hasProcess): ?>
            <div class="modal-tabs-container">
                <button class="gallery-tab-btn active" data-target="gallery-initial"><i class='bx bx-image'></i> Fotos Iniciales</button>
                <button class="gallery-tab-btn" data-target="gallery-process"><i class='bx bx-wrench'></i> Fotos del Proceso</button>
            </div>
            <?php endif; ?>

            <div class="modal-image-container" id="receipt-modal-viewport">
                <!-- 🛡️ Contenedores dobles para soportar Video o Imagen -->
                <img id="modal-current-image" src="" draggable="false" style="display:none;">
                
                <div id="modal-video-wrapper" style="display:none; position: relative; width: 100%; height: 100%; max-width: 800px; margin: 0 auto; box-shadow: 0 5px 25px rgba(0,0,0,0.5);">
                    <iframe id="modal-current-video" src="" width="100%" height="100%" frameborder="0" allow="autoplay; fullscreen" style="border-radius: 8px;"></iframe>
                    <!-- Escudo para tapar botón superior de Drive -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; height: 60px; background: transparent; z-index: 9999;"></div>
                </div>
            </div>

            <?php if ($hasInitial): ?>
                <div class="modal-thumbnails-wrapper" id="gallery-initial" style="<?php echo (!$hasInitial) ? 'display:none;' : ''; ?>">
                    <div class="modal-thumbnails">
                        <?php foreach($initialMedia as $index => $item): ?>
                            <div class="thumbnail-item <?php echo $index === 0 ? 'is-active' : ''; ?>" data-type="<?php echo $item['type']; ?>" data-fullsrc="<?php echo htmlspecialchars($item['type'] === 'video' ? str_replace('/view', '/preview', $item['src']) : getImageUrl($item['src'])); ?>">
                                <?php if ($item['type'] === 'video'): ?>
                                    <div style="width:100%; height:100%; background:#222; display:flex; align-items:center; justify-content:center; color:#fff;">
                                        <i class='bx bx-play-circle' style="font-size: 2.2rem;"></i>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars(getImageUrl($item['src'])); ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($hasProcess): ?>
                <div class="modal-thumbnails-wrapper" id="gallery-process" style="<?php echo ($hasInitial) ? 'display:none;' : ''; ?>">
                    <div class="modal-thumbnails">
                        <?php foreach($processMedia as $index => $item): ?>
                            <div class="thumbnail-item <?php echo (!$hasInitial && $index === 0) ? 'is-active' : ''; ?>" data-type="<?php echo $item['type']; ?>" data-fullsrc="<?php echo htmlspecialchars($item['type'] === 'video' ? str_replace('/view', '/preview', $item['src']) : getImageUrl($item['src'])); ?>">
                                <?php if ($item['type'] === 'video'): ?>
                                    <div style="width:100%; height:100%; background:#222; display:flex; align-items:center; justify-content:center; color:#fff;">
                                        <i class='bx bx-play-circle' style="font-size: 2.2rem;"></i>
                                    </div>
                                <?php else: ?>
                                    <img src="<?php echo htmlspecialchars(getImageUrl($item['src'])); ?>">
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- <script src="/assets/admin/js/receipts.js"></script> -->
    <script>
        function generatePDF() {
            document.getElementById('toolbar').style.display = 'none';
            const element = document.getElementById('invoice-content');
            const opt = {
                margin: 10, filename: '<?php echo $data['receipt_code']; ?>.pdf',
                image: { type: 'jpeg', quality: 0.98 },
                html2canvas: { scale: 2, useCORS: true }, 
                jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
            };
            html2pdf().set(opt).from(element).save().then(() => {
                document.getElementById('toolbar').style.display = 'flex';
            });
        }

        function openEmailModal() { document.getElementById('emailModal').style.display = 'flex'; }
        function closeEmailModal() { document.getElementById('emailModal').style.display = 'none'; }

        function openWaModal() { document.getElementById('waModal').style.display = 'flex'; }
        function closeWaModal() { document.getElementById('waModal').style.display = 'none'; }

        function sendCustomEmail() {
            const btn = document.getElementById('modal_btn_send');
            btn.innerText = "Enviando..."; btn.disabled = true;
            
            const customTo = document.getElementById('modal_to').value;
            const customSubject = document.getElementById('modal_subject').value;
            const customMessage = document.getElementById('modal_message').value;

            fetch('/admin/receipts/send-pdf', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    id: <?php echo $data['id_receipt']; ?>,
                    to_email: customTo, subject: customSubject, message: customMessage
                })
            })
            .then(res => res.text())
            .then(text => {
                try {
                    const data = JSON.parse(text);
                    alert(data.message);
                    closeEmailModal();
                    btn.innerText = "Enviar Enlace"; btn.disabled = false;
                } catch (e) {
                    alert("Error devuelto por PHP:\n" + text.substring(0, 150));
                    btn.innerText = "Enviar Enlace"; btn.disabled = false;
                }
            })
            .catch(err => {
                alert("Falló la conexión al servidor.");
                btn.innerText = "Enviar Enlace"; btn.disabled = false;
            });
        }

        function sendCustomWhatsApp() {
            let phone = document.getElementById('modal_wa_phone').value.trim();
            let message = document.getElementById('modal_wa_message').value;
            
            if (!phone) { alert("Ingresa un número válido."); return; }

            fetch('/admin/receipts/mark-wa-sent', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: <?php echo $data['id_receipt']; ?> })
            });

            let encodedMessage = encodeURIComponent(message);
            window.open(`https://wa.me/${phone}?text=${encodedMessage}`, '_blank');
            closeWaModal();
            location.reload(); 
        }

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'email') {
                openEmailModal(); 
            } else if (urlParams.get('action') === 'whatsapp') {
                openWaModal(); 
            }
        });

        function scalePDF() {
            const wrapper = document.querySelector('.invoice-wrapper');
            if (!wrapper) return;

            const screenWidth = window.innerWidth || document.documentElement.clientWidth;

            if (screenWidth <= 820) {
                wrapper.style.width = '800px';
                wrapper.style.minWidth = '800px';
                wrapper.style.maxWidth = 'none';
                wrapper.style.margin = '0';
                
                const scaleFactor = (screenWidth - 20) / 800;
                
                wrapper.style.transformOrigin = 'top left';
                wrapper.style.transform = `scale(${scaleFactor})`;
                wrapper.style.marginLeft = '10px';
                
                const realHeight = wrapper.offsetHeight;
                const scaledHeight = realHeight * scaleFactor;
                wrapper.style.marginBottom = `-${realHeight - scaledHeight}px`;

            } else {
                wrapper.style.width = '100%';
                wrapper.style.minWidth = '0';
                wrapper.style.maxWidth = '800px';
                wrapper.style.transform = 'none';
                wrapper.style.marginLeft = 'auto';
                wrapper.style.marginRight = 'auto';
                wrapper.style.marginBottom = '0';
            }
        }
        scalePDF();
        window.addEventListener('resize', scalePDF);

        // ==================================================================
        // VISOR MODAL DE IMÁGENES/VIDEOS (Lógica inyectada)
        // ==================================================================
        const modal = document.getElementById('product-gallery-modal');
        const btnOpenGallery = document.getElementById('btn-open-gallery');
        
        if (modal && btnOpenGallery) {
            const closeBtn = document.getElementById('receipt-modal-close');
            const prevBtn = document.getElementById('receipt-modal-prev');
            const nextBtn = document.getElementById('receipt-modal-next');
            const mainImg = document.getElementById('modal-current-image');
            const mainVid = document.getElementById('modal-current-video');
            const tabs = modal.querySelectorAll('.gallery-tab-btn');
            const galleryWrappers = modal.querySelectorAll('.modal-thumbnails-wrapper');
            
            let currentThumbnails = []; 
            let currentIndex = 0;

            const updateImage = () => {
                if (currentThumbnails.length === 0) return;
                currentThumbnails.forEach(t => t.classList.remove('is-active'));
                const activeThumb = currentThumbnails[currentIndex];
                activeThumb.classList.add('is-active');
                
                const type = activeThumb.getAttribute('data-type');
                const src = activeThumb.getAttribute('data-fullsrc');

                if (type === 'video') {
                    mainImg.style.display = 'none';
                    document.getElementById('modal-video-wrapper').style.display = 'block';
                    document.getElementById('modal-current-video').src = src;
                } else {
                    document.getElementById('modal-video-wrapper').style.display = 'none';
                    document.getElementById('modal-current-video').src = '';
                    mainImg.style.display = 'block';
                    mainImg.src = src;
                }

                activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
            };

            const switchTab = (targetId) => {
                tabs.forEach(t => t.classList.remove('active'));
                const activeTab = modal.querySelector(`.gallery-tab-btn[data-target="${targetId}"]`);
                if (activeTab) activeTab.classList.add('active');

                galleryWrappers.forEach(w => w.style.display = 'none');
                const targetWrapper = document.getElementById(targetId);
                if (targetWrapper) {
                    targetWrapper.style.display = 'flex';
                    currentThumbnails = targetWrapper.querySelectorAll('.thumbnail-item');
                    currentIndex = 0; 
                    updateImage();
                }
            };

            tabs.forEach(tab => {
                tab.addEventListener('click', (e) => {
                    e.stopPropagation(); switchTab(tab.getAttribute('data-target'));
                });
            });

            const defaultTab = modal.querySelector('.gallery-tab-btn.active');
            if (defaultTab) {
                currentThumbnails = document.getElementById(defaultTab.getAttribute('data-target')).querySelectorAll('.thumbnail-item');
            } else if (galleryWrappers.length > 0) {
                currentThumbnails = galleryWrappers[0].querySelectorAll('.thumbnail-item'); 
            }

            const openModal = () => {
                if(modal.querySelectorAll('.thumbnail-item').length === 0) return;
                modal.style.display = 'flex'; 
                document.body.style.overflow = 'hidden'; 
                updateImage();
            };

            const closeModal = () => {
                modal.style.display = 'none'; 
                document.body.style.overflow = '';
                document.getElementById('modal-current-video').src = '';
            };

            btnOpenGallery.addEventListener('click', (e) => { e.preventDefault(); openModal(); });
            if(closeBtn) closeBtn.addEventListener('click', closeModal);

            if (prevBtn) {
                prevBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if(currentThumbnails.length === 0) return;
                    currentIndex = (currentIndex === 0) ? currentThumbnails.length - 1 : currentIndex - 1;
                    updateImage();
                });
            }

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    e.stopPropagation();
                    if(currentThumbnails.length === 0) return;
                    currentIndex = (currentIndex === currentThumbnails.length - 1) ? 0 : currentIndex + 1;
                    updateImage();
                });
            }

            modal.addEventListener('click', (e) => {
                const thumb = e.target.closest('.thumbnail-item');
                if (thumb) {
                    e.stopPropagation();
                    const index = Array.from(currentThumbnails).indexOf(thumb);
                    if (index !== -1) { currentIndex = index; updateImage(); }
                    return;
                }
                if (e.target !== mainImg && e.target !== mainVid && !e.target.closest('.modal-tabs-container') && !e.target.closest('.modal-prev-btn') && !e.target.closest('.modal-next-btn') && !e.target.closest('#receipt-modal-close')) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', (e) => {
                if (modal.classList.contains('is-open') || modal.style.display === 'flex') {
                    if (e.key === 'Escape') closeModal();
                    if (e.key === 'ArrowLeft' && prevBtn) prevBtn.click();
                    if (e.key === 'ArrowRight' && nextBtn) nextBtn.click();
                }
            });
        }
    </script>
</body>
</html>