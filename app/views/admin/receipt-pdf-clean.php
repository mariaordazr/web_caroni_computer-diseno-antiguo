<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nota de Recibo - <?php echo htmlspecialchars($data['receipt_code']); ?></title>
    <style>
        /* CSS Clásico y Profesional (Gris/Negro) */
        body { font-family: 'Inter', Arial, sans-serif; font-size: 12px; color: #333; margin: 0; padding: 0; }
        table { width: 100%; border-collapse: collapse; }
        
        /* --- HEADER --- */
        .header-table { margin-top: 55px; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header-table td { vertical-align: middle; }
        .company-info { text-align: right; line-height: 1.4; }
        .company-name { font-size: 16px; font-weight: bold; color: #000; margin-bottom: 5px; text-transform: uppercase;}
        
        /* --- META (Recibimos de / Nota de recibo) --- */
        .meta-table { margin-bottom: 20px; }
        .meta-table td { vertical-align: top; padding: 10px; border: 1px solid #ccc; background-color: #f9f9f9; }
        .box-title { font-weight: bold; font-size: 13px; color: #000; margin-bottom: 8px; border-bottom: 1px solid #ccc; padding-bottom: 4px; text-transform: uppercase; }
        
        /* --- TABLAS DE EQUIPOS Y PRESUPUESTO --- */
        .items-table { margin-bottom: 20px; }
        .items-table th { background-color: #333; color: white; font-weight: bold; padding: 8px 5px; text-align: left; font-size: 10px; text-transform: uppercase; border: 1px solid #333;}
        .items-table td { border: 1px solid #ccc; padding: 8px 5px; vertical-align: top; }
        
        /* --- NOTAS IMPORTANTES --- */
        .notes-section { margin-top: 15px; font-size: 10px; line-height: 1.4;}
        .notes-label { font-weight: bold; color: #000; }
        .notes-block { margin-bottom: 8px; }
        
        /* --- FOOTER INLINE (Pegado al contenido) --- */
        @page { margin: 40px 40px 40px 40px; } /* Márgenes uniformes */
        .pdf-footer-clean { margin-top: 40px; font-size: 9px; text-align: center; color: #777; border-top: 1px solid #ccc; padding-top: 5px; }
    </style>
</head>
<body>
    <main>
        <table class="header-table">
            <tr>
                <td style="width: 40%;">
                    <?php
                        $logoPath = ROOT_PATH . 'public/assets/uploads/logo/gruponet.webp';
                        if (file_exists($logoPath)) {
                            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                            $dataImg = file_get_contents($logoPath);
                            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($dataImg);
                            echo '<img src="' . $base64 . '" style="max-width: 180px; max-height: 70px;">';
                        } else {
                            echo '<h2 style="color:#000; margin:0;">CARONI COMPUTER</h2>';
                        }
                    ?>
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
                <?php if(!empty($items)): foreach($items as $item): ?>
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
            
            <div style="margin-top: 20px; font-size: 9px; text-align: center; color: #777; border-top: 1px solid #ccc; padding-top: 5px;">
                Este documento constituye un control de mercancía recibida. | Generado el <?php echo date('d/m/Y H:i', strtotime($data['created_at'] ?? date('Y-m-d H:i:s'))); ?>
            </div>
        </div>
    </main>
</body>
</html>