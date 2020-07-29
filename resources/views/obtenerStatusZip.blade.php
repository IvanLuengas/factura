<!DOCTYPE html>
<html>
<head>
<title>Factura Superintendencia</title>
</head>
<body>
	<h1>Superintendencia de Notariado y registro</h1>
	<h2>Factura firmada</h2>
	<?php echo $firma->saveXML().'<p>';?>	
	<button type="button" onclick="window.location='{{ route("factura") }}'">Nueva</button>
</body>
</html>