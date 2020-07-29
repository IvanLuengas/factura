<!DOCTYPE html>
<html>
<head>
	<title>Factura Superintendencia</title>
</head>
<body>
	<h1>Superintendencia de Notariado y registro</h1>
	<table>
	<form method="POST">
		@csrf
			<p><label name="Notaria">Notaria</label><input name="notaria" value='<?php echo($notaria) ?>' disabled></p>
			<p><label name="Notario:">Notario:</label><input name="CompanyName" value='<?php echo($CompanyName) ?>'disabled>
			<label name="NIT:">NIT:</label><input name="NITR" value='<?php echo($NITR) ?>'disabled></p>
			<p><label name="Direción:">Direción:</label><input name="CompanyAddress" value='<?php echo($CompanyAddress) ?>'disabled>	
			<label name="telefono:">telefono:</label><input name="telefono" value='<?php echo($telefono) ?>'disabled></p>		
			<p><label name="E-mail:">E-mail:</label><input name="EmailR" type="email" value='<?php echo($EmailR) ?>'disabled>	
			<label name="Regimen:">Regimen:</label><input name="regimen" value='<?php echo($regimen) ?>'disabled></p>	

			<p><label name="N° Factura">N° Factura</label><input name="ID" value='<?php echo($ID) ?>'disabled></p>
			<p><label name="Cliente">Cliente</label><input name="CustomerName" value='<?php echo($CustomerName) ?>'>
			<label name="NIT:">NIT:</label><input name="CustomerNit" value='<?php echo($CustomerNit) ?>'></p>
			<p><label name="E-mail:">E-mail:</label><input tyoe="email" name="CustomerEmail" value='<?php echo($CustomerEmail) ?>'></p>

			<p><label name="Fecha:">Fecha:</label><input name="IssueDate" value='<?php echo($IssueDate) ?>'disabled>
				<label name="Escritura N°:">Escritura N°:</label><input name="publicDeed" value='<?php echo($publicDeed) ?>'>				
				<label name="Pagada con recibo N°:">Pagada con recibo N°:</label><input name="ID" value='<?php echo($ID) ?>'disabled></p>

				<p><label name="Actos">Actos</label></p>
				<p><input name="publicDocument" value='<?php echo($publicDocument) ?>'><input name="LineExtensionAmount" value='<?php echo($LineExtensionAmount) ?>'></p>

				<p><label name="Concepto">Concepto</label></p>
				<p><input name="codImp1" value='<?php echo($codImp1) ?>'><input name="ValImp1" value='<?php echo($ValImp1) ?>'></p>
				<p><input name="codImp2" value='<?php echo($codImp2) ?>'><input name="ValImp2" value='<?php echo($ValImp2) ?>'></p>
				<p><input name="codImp3" value='<?php echo($codImp3) ?>'><input name="ValImp3" value='<?php echo($ValImp3) ?>'></p>
				<p><input name="codImp4" value='<?php echo($codImp4) ?>'><input name="ValImp4" value='<?php echo($ValImp4) ?>'></p>
				<p><input name="codImp5" value='<?php echo($codImp5) ?>'><input name="ValImp5" value='<?php echo($ValImp5) ?>'></p>
				<p><input name="codImp6" value='<?php echo($codImp6) ?>'><input name="ValImp6" value='<?php echo($ValImp6) ?>'></p>
				<p><input name="codImp7" value='<?php echo($codImp7) ?>'><input name="ValImp7" value='<?php echo($ValImp7) ?>'></p>
				<p><label name="Pago:">Pago:</label><input name="TaxExclusiveAmount" value='<?php echo($TaxExclusiveAmount) ?>'></p>

				<p><label name="Valor:">Valor:</label><input name="PayableAmount" value='<?php echo($PayableAmount) ?>'></p>
			 <button type="button" onclick="window.location='{{ route("factura/firmarFactura") }}'">Firmar</button>
			</form>
		</table>
	</body>
	</html>