<!DOCTYPE html>
<html>
<head>
	<title>Factura Superintendencia</title>
</head>
<body>
	<h1>Superintendencia de Notariado y registro</h1>
	<h2>Factura firmada</h2>
	<?php echo $firma.'<p>';?>	
	<button type="button" onclick="window.location='{{ route("factura/obtenerStatusZip") }}'">Exportar y Enviar</button>
</body>
</html>