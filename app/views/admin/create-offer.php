<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>

            <section class="content-panel">
                <form class="edit-form" method="POST" action="/admin/storeOffer" enctype="multipart/form-data">
                    
                    <div class="form-grid">
                        <div class="form-column">
                            <label for="message">Mensaje para la Oferta:</label>
                            <input type="text" id="message" name="message" required>

                            <label for="price">Precio de la Oferta ( <?php echo htmlspecialchars($moneySymbol); ?> ):</label>
                            <input type="number" id="price" name="price" step="0.01" placeholder="0.00" required>
                        </div>
                        <div class="form-column">
                            <label for="product">Producto:</label>
                            <select id="product" name="product" required>
                                <option value="" disabled selected>Selecciona el producto</option>
                                <?php foreach($headerProducts as $product): ?>
                                    <option value="<?php echo $product['id_product']; ?>"><?php echo htmlspecialchars(ucfirst(mb_strtolower($product['name']))); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
<?php include_once ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>