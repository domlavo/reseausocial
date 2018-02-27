<?php

function renderFooter() {
	ob_start();
	?>
	</body>
	</html>
	<?php
	return ob_get_clean();
}

?>