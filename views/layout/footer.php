        </div>
    </div>
</div>
<script src="public/js/app.js?v=<?php echo filemtime(__DIR__ . '/../../public/js/app.js'); ?>"></script>
<?php if (!empty($_SESSION['flash'])): ?>
<script>alert(<?php echo json_encode($_SESSION['flash']); ?>);</script>
<?php unset($_SESSION['flash']); endif; ?>
</body>
</html>
