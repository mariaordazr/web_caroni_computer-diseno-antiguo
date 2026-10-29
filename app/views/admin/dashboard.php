<?php include_once ROOT_PATH . 'app/views/admin/templates/header.php'; ?>
<section class="dashboard-widgets grid-2-col">
    <div class="widget total-products">
        <div class="widget__icon" style="background-color: #e0f2f7;">
            <i class='bx bx-package' style="color: #03a9f4;"></i>
        </div>
            <div  class="widget__info">
                <h3>Total Productos</h3>
                <p class="widget-value"><?php echo htmlspecialchars(number_format($totalProducts ?? 0)); ?></p>
            </div>
        </a>
        
    </div>
    <div class="widget new-arrivals">
        <div class="widget__icon" style="background-color: #ffe0b2;">
            <i class='bx bx-star' style="color: #ff9800;"></i>
        </div>
        <div class="widget__info">
            <h3>Recién Llegados</h3>
            <p class="widget-value"><?php echo htmlspecialchars(number_format($newArrivals ?? 0)); ?></p>
        </div>
    </div>
    <div class="widget out-of-stock">
        <div class="widget__icon" style="background-color: #ffcdd2;">
            <i class='bx bx-x-circle' style="color: #f44336;"></i>
        </div>
        <div class="widget__info">
            <h3>Fuera de Stock</h3>
            <p class="widget-value"><?php echo htmlspecialchars(number_format($outOfStock ?? 0)); ?></p>
        </div>
    </div>
    <div class="widget low-stock">
        <div class="widget__icon" style="background-color: #fff9c4;">
            <i class='bx bx-low-vision' style="color: #ffeb3b;"></i>
        </div>
        <div class="widget__info">
            <h3>Bajo Stock</h3>
            <p class="widget-value"><?php echo htmlspecialchars(number_format($lowStock ?? 0)); ?></p>
        </div>
    </div>

<?php include ROOT_PATH . 'app/views/admin/templates/footer.php'; ?>