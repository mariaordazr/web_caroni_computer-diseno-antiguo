<?php include ROOT_PATH . 'app/views/templates/header.php'; ?>

<main class="main-content">
    <?php if ($product && $product['product_hidden']==0): ?>
        <div class="container">
            <div class="product-detail-layout">

                <div class="product-gallery">
                    <?php 
                        // Extraemos el ID de Google Drive
                        $driveId = '';
                        if (!empty($product['video_url'])) {
                            if (preg_match('/[-\w]{25,}/', $product['video_url'], $match)) {
                                $driveId = $match[0];
                            }
                        }
                    ?>
                    <div class="gallery-thumbnails">
                        <?php foreach ($images as $index => $image): ?>
                            <!-- Indicamos data-type="image" -->
                            <div class="thumbnail-item <?php echo ($index === 0) ? 'is-active' : ''; ?>" data-type="image">
                                <?php 
                                    $originalFile = !empty($image['file_name']) ? $image['file_name'] : 'default.jpg';
                                    $showWebp = false;
                                    $smallFile = '';
                                    
                                    if ($originalFile !== 'default.jpg') {
                                        $nameWithoutExt = pathinfo($originalFile, PATHINFO_FILENAME);
                                        $smallFile = 'small_' . $nameWithoutExt . '.webp';
                                        if (file_exists(ROOT_PATH . 'public/assets/uploads/products/' . $smallFile)) {
                                            $showWebp = true;
                                        }
                                    }
                                ?>
                                <picture>
                                    <?php if ($showWebp): ?>
                                        <source srcset="/assets/uploads/products/<?php echo rawurlencode($smallFile); ?>" type="image/webp">
                                    <?php endif; ?>
                                    <img src="/assets/uploads/products/<?php echo rawurlencode($originalFile); ?>" alt="imagen <?php echo $index + 1; ?>" loading="<?php echo ($index === 0) ? 'eager' : 'lazy'; ?>">
                                </picture>
                            </div>
                        <?php endforeach; ?>

                        <!-- MINIATURA DEL VIDEO -->
                        <?php if ($driveId): ?>
                            <div class="thumbnail-item video-thumbnail" data-type="video" data-video-id="<?php echo $driveId; ?>" style="position: relative; display: flex; align-items: center; justify-content: center; background: #000;">
                                <i class='bx bx-play-circle' style="color: #fff; font-size: 2.2rem; position: absolute; z-index: 2;"></i>
                                <img src="https://drive.google.com/thumbnail?id=<?php echo $driveId; ?>&sz=w200" style="width: 100%; height: 100%; object-fit: cover; opacity: 0.5;" alt="Video del Producto">
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="gallery-main-image" style="position: relative;">
                        <!-- INYECCIÓN DEL SELLO PREMIUM -->
                        <?php if (isset($product['certification']) && $product['certification'] === 'premium'): ?>
                            <img src="/assets/uploads/logo/premium-seal.png" class="premium-seal" alt="Certificado Premium">
                        <?php endif; ?>
                        <!-- Imagen Principal -->
                        <img id="main-product-image" src="/assets/uploads/products/<?php echo htmlspecialchars($images[0]['file_name'] ?? 'default.png'); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" loading="<?php echo $first ? 'eager' : 'lazy'; ?>" <?php echo $first ? 'fetchpriority="high"' : ''; ?> width="1200" height="400">
                        
                        <!-- Reproductor de Video Oculto -->
                        <?php if ($driveId): ?>
                            <div id="main-product-video" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #000; overflow: hidden; border-radius: 6px;">
                                <iframe id="video-iframe" src="" allow="autoplay; fullscreen" allowfullscreen style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none;"></iframe>
                                <!-- Escudo protector para ocultar el botón externo -->
                                <div style="position: absolute; top: 0; right: 0; width: 65px; height: 65px; z-index: 10; background: transparent; cursor: default;" title="Ventana externa deshabilitada"></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Panel de Información del Video (Oculto por defecto) -->
                    <?php if ($driveId && (!empty($product['video_title']) || !empty($product['video_description']))): ?>
                        <div id="video-info-panel" style="display: none; margin-top: 0.5rem; padding: 1rem; background: #fdfdfd; border: 1px solid #eee; border-radius: 6px; grid-column: 1 / -1;">
                            <?php if (!empty($product['video_title'])): ?>
                                <h3 style="font-size: 1.1rem; margin-bottom: 0.5rem; color: var(--color-primary);"><?php echo htmlspecialchars($product['video_title']); ?></h3>
                            <?php endif; ?>
                            <?php if (!empty($product['video_description'])): ?>
                                <p style="font-size: 0.9rem; color: #555; margin: 0; line-height: 1.5;"><?php echo nl2br(htmlspecialchars($product['video_description'])); ?></p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div id="product-gallery-modal" class="modal">
                    <span class="modal-close-btn">&times;</span>
                    
                    <div class="modal-content">
                        <!-- EL CANVAS ENVUELVE LA IMAGEN -->
                        <div class="zoom-canvas" id="zoom-canvas">
                            <img id="modal-current-image" src="" alt="Imagen de la galería">
                        </div>
                        <a class="modal-prev-btn">&#10094;</a>
                        <a class="modal-next-btn">&#10095;</a>
                    </div>

                    <div class="modal-thumbnails">
                    </div>
                </div>
                <div class="product-info">
                    <h1 class="product-info__title"><?php echo htmlspecialchars($product['name']); ?></h1>

                    <?php if ($showPrice=='1'): ?>
                        <div class="product-info__price">
                            <?php if ($product['offer_price'] !== null && $product['offer_price'] < $product['original_price']): ?>
                                <span class="original-price"><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['original_price'], 2)); ?></span>
                                <div class="offer-line">
                                    <span class="offer-price"><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['offer_price'], 2)); ?></span>
                                    <?php $discount = round((($product['original_price'] - $product['offer_price']) / $product['original_price']) * 100); ?>
                                    <span class="discount-tag"><?php echo $discount; ?>% descuento</span>
                                </div>
                            <?php else: ?>
                                <span class="offer-price"><?php echo htmlspecialchars($moneySymbol); ?> <?php echo htmlspecialchars(number_format($product['original_price'], 2)); ?></span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($showStock): ?>
                        <div class="product-info__stock">
                            <?php $valeryFlag = ($product['valery_connected'] == 1) ? '*' : ''; ?>
                            <?php if ($product['stock'] > 0): ?>
                                <span class="stock-available">Stock disponible <?php echo $valeryFlag; ?></span>
                            <?php else: ?>
                                <span class="stock-unavailable">Agotado <?php echo $valeryFlag; ?></span>
                            <?php endif; ?>
                        </div>
                    
                        <?php if ($product['stock'] > 0): ?>
                            <div class="product-info__quantity">
                                <label for="quantity">Cantidad:</label>
                                <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                                
                                <span>(<?php echo $product['stock']; ?> disponibles)</span>
                            </div>
                        <?php else: ?>
                            <div class="product-info__quantity">
                                <label for="quantity" style="font-style: italic;">Puede consultar para solicitar bajo pedido</label>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($product['warranty'])): ?>
                            <div class="product-info__warranty">
                                <label>Garantía:</label>
                                <span><?php echo $product['warranty']; ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <div class="product-description-section main-summary">
                        <span>SKU: <?php echo $product['sku']; ?></span>
                        <?php if (!empty($product['description'])): ?>
                            <h2 style="font-size: 0.9rem;padding-top: 1rem;padding-bottom: 0.5rem;">Resumen</h2>
                        
                            <ul style="margin-top:0;padding-top:0;padding-left:0.2rem;list-style-type:square;">
                                <?php
                                // Obtenemos la descripción del producto
                                $description = $product['description'] ?? '';

                                // Convertimos la cadena de texto en un array, separando por cada salto de línea
                                $features = explode("\n", str_replace("\r\n", "\n", $description));

                                // Recorremos cada línea y la convertimos en un elemento de la lista
                                foreach ($features as $feature):
                                    // Nos aseguramos de que la línea no esté vacía antes de mostrarla
                                    $feature = trim($feature);
                                    if (!empty($feature)):
                                ?>
                                    <li style="font-size: 0.7rem; display: -webkit-box; list-style-type: square; -webkit-box-orient: vertical; -webkit-line-clamp: 2; overflow: hidden; text-overflow: ellipsis; white-space: normal; word-break: break-word;">
                                        - <?php echo htmlspecialchars($feature); ?>
                                    </li>
                                <?php
                                    endif;
                                endforeach;
                                ?>
                            </ul>
                        <?php endif; ?>
                    </div>


                    <div class="product-info__actions" style="margin-top: 1.5rem; border-top: 1px solid #ddd; padding-top: 1rem;">
                        <button class="btn btn-primary btn-buy-now">
                            <?php 
                                // 1. Armamos la URL actual completa del producto
                                $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
                                $host = $_SERVER['HTTP_HOST'];
                                $uri = $_SERVER['REQUEST_URI'];
                                $currentUrl = $protocol . "://" . $host . $uri;

                                // 2. Preparamos el mensaje final incluyendo la URL
                                if ($product['stock'] > 0) {
                                    $whatsappMessage = 'Hola, quiero comprar *' . $product['name'] . '* ¿Puedes proporcionarme más detalles? Aquí está el enlace: ' . $currentUrl;
                                    $buttonText = 'Comprar ahora por WhatsApp';
                                } else {
                                    $whatsappMessage = 'Hola, quiero realizar un pedido de *' . $product['name'] .'* que se encuentra agotado. Aquí está el enlace: ' . $currentUrl;
                                    $buttonText = 'Hacer pedido ahora por WhatsApp';
                                }
                            ?>

                            <a href="https://wa.me/<?php echo htmlspecialchars(preg_replace('/\D+/', '', $contactPhone));?>?text=<?php echo urlencode($whatsappMessage); ?>" target="_blank" title="WhatsApp">
                                <?php echo $buttonText; ?> <i class='bx bxl-whatsapp'></i>
                            </a>

                        </button>
                    </div>
                </div>

                <div class="product-description-section">
                    <?php if (!empty($product['description'])): ?>
                        <h2>Características principales</h2>
                        
                        <table class="features-table">
                            <tbody>
                                <?php
                                $description = $product['description'] ?? '';
                                // Separamos por saltos de línea
                                $features = explode("\n", str_replace("\r\n", "\n", $description));

                                foreach ($features as $feature):
                                    // Ahora, dividimos cada línea por el PRIMER ':' que encuentre
                                    $parts = explode(':', $feature, 2);
                                    
                                    // Verificamos que tengamos 2 partes (clave y valor)
                                    if (count($parts) === 2):
                                        $key = trim($parts[0]);
                                        $value = trim($parts[1]);
                                        
                                        // Solo mostramos la fila si ambos lados tienen contenido
                                        if (!empty($key) && !empty($value)):?>
                                            <tr>
                                                <th><?php echo htmlspecialchars($key); ?></th>
                                                <td><?php echo htmlspecialchars($value); ?></td>
                                            </tr>
                                        <?php endif;
                                    else:
                                        $feature = trim($feature);
                                        if (!empty($feature)):?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($feature); ?></td>
                                            </tr>
                                        <?php
                                            endif;
                                    endif;
                                endforeach;?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                    <?php if (!empty($product['ext_description'])): ?>
                        <h2 style="padding-top: 2rem;padding-bottom: 0.25rem;">Descripción</h2>
                        <div class="product-extended-description" style="padding: 0.5rem;">
                            <?php 
                                // Usamos nl2br() para convertir los saltos de línea
                                // del textarea en etiquetas <br>
                                echo nl2br(htmlspecialchars($product['ext_description'])); 
                            ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="no-results-message" style="text-align: center; padding: 4rem 1rem;">
            <i class='bx bx-search-alt'></i>
            <h2>No se encontraron productos</h2>
            <p>Prueba a cambiar los filtros o a restablecer la búsqueda.</p>
        </div>
    <?php endif; ?>
</main>

<script src="/assets/js/product-detail.js"></script>

<?php include ROOT_PATH . 'app/views/templates/footer.php'; ?>