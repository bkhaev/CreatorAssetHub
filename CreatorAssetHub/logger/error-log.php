<?php
	// Logger handler for JS-files

    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
	header("HTTP/1.1 201 Created");

    function report_js($errstr, $errline, $errcolumn, $errfile)
	{
		$content = "
		<table>
			<thead><th>Item</th><th>Description</th></thead>
			<tbody>
				<tr>
					<th>Error</th>
					<td><pre>$errstr</pre></td>
				</tr>
				<tr>
					<th>Line</th>
					<td><pre>$errline</pre></td>
				</tr>
				<tr>
					<th>Column</th>
					<td><pre>$errcolumn</pre></td>
				</tr>
				<tr>
					<th>File</th>
					<td><pre>$errfile</pre></td>
				</tr>
			</tbody>
		</table>";

		return $content;
	}
?>