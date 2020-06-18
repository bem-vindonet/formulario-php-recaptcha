<?php
/*
The MIT License (MIT)

Copyright (c) 2020 Dario Gomes para bem-vindo.net

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

/**** ALTERAR OS CAMPOS ABAIXO ****/
// informar as chaves do ReCaptcha abaixo:
$chave_de_site = 'INSIRA-AQUI-A-CHAVE-DO-SITE';
$chave_secreta = 'INSIRA-AQUI-A-CHAVE-SECRETA';

// definir
$destinatario = 'seuemail@seudominio.com.br';
$remetente = 'formulario@seudominio.com.br';
$assunto = 'Contato pelo site';
$redirecionar_para = '/obrigado.html';
/**** FIM DAS ALTERAÇÕES ****/

if (isset($_POST['enviar']))
{
	// incluir a funcionalidade do recaptcha
	require_once('recaptchalib.php');

	$erros = [];
	if (empty($_POST['nome']))
		$erros[] = 'Nome não preenchido';

	if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
		$erros[] = 'E-mail não preenchido ou inválido';

	if (empty($_POST['cidade']))
		$erros[] = 'Cidade não preenchida';

	if (empty($_POST['assunto']))
		$erros[] = 'Assunto não informado';

	if (empty($_POST['mensagem']))
		$erros[] = 'Mensagem não fornecida';

	// verificar a chave secreta
	$response = null;
	$reCaptcha = new ReCaptcha($chave_secreta);

	if ($_POST['g-recaptcha-response'])
		$response = $reCaptcha->verifyResponse($_SERVER['REMOTE_ADDR'], $_POST['g-recaptcha-response']);

	if ($response == null || !$response->success)
		$erros[] = 'Erro na verificação do Recaptcha';

	if (!$erros)
	{
		$ip = $_SERVER['REMOTE_ADDR'];
		$reverso = gethostbyaddr($ip);
		if ($reverso == $ip)
			$origem = $ip;
		else
			$origem = "$ip ($reverso)";
		$de = "\"$_POST[nome]\" <$_POST[email]>";

		$corpo = "Origem: $origem
Navegador: $_SERVER[HTTP_USER_AGENT]

De: $de
Cidade: $_POST[cidade]
Assunto: $_POST[assunto]

Mensagem:
$_POST[mensagem]";

		$headers = "From: $remetente\n";
		$headers .= "Reply-To: $de";

		if (mail($destinatario, $assunto, $corpo, $headers, "-f$remetente"))
		{
			header("Location: $redirecionar_para");
			exit;
		}
		else
			$erros[] = 'Erro ao mandar seu e-mail, por favor tente novamente mais tarde';
	}
}

?>
<!doctype html>
<html lang="pt-br">
<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

	<!-- Bootstrap CSS -->
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">

	<script src="https://www.google.com/recaptcha/api.js?hl=pt-BR"></script>

	<title>Formulário de contato</title>
</head>
<body>
	<div class="container">
		<h1>Formulário de contato</h1>

	<?php
if (!empty($erros))
{
?>
		<div class="alert alert-danger" role="alert">
			Seu formulário não foi enviado:
			<ul class="mb-0">
<?php
	foreach ($erros as $erro)
		echo '<li>' . htmlspecialchars($erro) . "</li>\n";
?>
			</ul>
		</div>
<?php
}
?>
		<form method="POST">
			<div class="form-group">
				<label for="nome">Seu nome</label>
				<input type="text" class="form-control" id="nome" name="nome" value="<?php echo htmlspecialchars($_POST['nome']); ?>" required>
			</div>
			<div class="form-group">
				<label for="email">E-mail</label>
				<input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email']); ?>" required>
			</div>
			<div class="form-group">
				<label for="cidade">Cidade</label>
				<input type="text" class="form-control" id="cidade" name="cidade" value="<?php echo htmlspecialchars($_POST['cidade']); ?>" required>
			</div>
			<div class="form-group">
				<label for="assunto">Assunto</label>
				<input type="text" class="form-control" id="assunto" name="assunto" value="<?php echo htmlspecialchars($_POST['assunto']); ?>" required>
			</div>
			<div class="form-group">
				<label for="mensagem">Mensagem</label>
				<textarea class="form-control" id="mensagem" rows="5" name="mensagem" required><?php echo htmlspecialchars($_POST['mensagem']); ?></textarea>
			</div>
			<div class="form-group">
				<div class="g-recaptcha" data-sitekey="<?php echo $chave_de_site; ?>"></div>
			</div>
			<button type="submit" class="btn btn-primary" name="enviar" value="Enviar">Enviar</button>
		</form>
	</div>

	<!-- Optional JavaScript -->
	<!-- jQuery first, then Popper.js, then Bootstrap JS -->
	<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>

</body>
</html>
