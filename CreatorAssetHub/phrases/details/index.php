<?php
	require_once '../../service/bflib.php';
	
	if (!(isset($_GET['phrase'])) || $_GET['phrase'] == false)
	{
		header('Location: ../phrases');
		exit(); //опционально
	}
	
	if (isset($_POST['key']))
	{
		$hash = '';
		$editphr = checkPhrKey($_POST['key'], $_GET['phrase'], $hash);
		if ($editphr === true)
		{
			header("Location: ../phrases/addphrase?phrase={$_GET['phrase']}&key=$hash#");
			exit(); //опционально
		}
		else if ($editphr === false)
		{
			header("Location: ../phrases/details?phrase={$_GET['phrase']}&keyfail#edit");
			exit(); //опционально
		}
		else
			trigger_error('PEA-8', E_USER_WARNING);
	}

	getBaseUserData();
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Информация о фразе - Creator Asset Hub</title>
<link href="../../favicon/favicon.png" rel="icon"  type="image/png" sizes="16x16"/>
<link href="https://use.typekit.net/aar7yxx.css" rel="stylesheet">
<link href="../../css/index1.css" rel="stylesheet" type="text/css">
<link href="../../css/main1.css" rel="stylesheet" type="text/css">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="../../js/imask.js"></script>
<script src="../../js/index1.js" type="text/javascript"></script>
<script src="../../js/datalib.js" type="text/javascript"></script>
<script src="../../js/phrasedtls.js" type="text/javascript" defer></script>
</head>

<body>
	<?php include_once "../../service/navbar.php"; ?>

	<div class="overlay" id="edit">
		<div class="modal <?php if (isset($_GET['keyfail'])) echo 'failed'; ?>">
			<div class="helpmodal" id="keyHelpInfo">
				<p>Используйте ключ, который выдаётся при добавлении фразы.</p>
			</div>
			<form method="post">
				<label for="phrkey">Введите ключ:</label>
				<input class="input3" type="text" id="phrkey" name="key" style="text-align: center;">
				<?php if (isset($_GET['keyfail'])) echo '<span>Неверный ключ</span>'; ?>
				<input class="btn btn-primary" type="submit" value="Готово">
			</form>
		</div>
	</div>

	<br><br>
	
	<div class="main1 tableflex1" id="upInfoVid">
		<nav class="nav1">
			<li><a href="../">Главная</a></li>
			<li><a href="../phrases">Фразы</a></li>
			<li></li>
		</nav>
		<div id="statusInfo">
			<li>Просмотров:&nbsp;</li>
			<li>-</li>
		</div>
	</div>
	
	<br><br>
	
	<main class="main1">
		<div id="infoVid">
			<div id="phr-video"></div>
			<div class="tableflex1">
				<div>
					<li>Добавил пользователь</li><br>
					<li class="stats"><img id="userPicture" src="<?php echo constant('sysprepath').'images/ava1.png'; ?>" alt="Profile" width="35px" style="border-radius: 50%;"></li>
					<li id="infoVidUser">-</li>
					<table id="phrUpTable" cellspacing="0" cellpadding="0">
						<tbody>
							<tr valign="top">
								<td class="phr-th">Автор:</td>
								<td class="phr-td">-</td>
							</tr>
							<tr valign="top">
								<td class="phr-th">Тон:</td>
								<td class="phr-td">-</td>
							</tr>
							<tr valign="top">
								<td class="phr-th">Эмоции:</td>
								<td class="phr-td">-</td>
							</tr>
							<tr valign="top">
								<td class="phr-th">Начало:</td>
								<td class="phr-td">-</td>
							</tr>
							<tr valign="top">
								<td class="phr-th">Web-камера:</td>
								<td class="phr-td">-</td>
							</tr>
						</tbody>
					</table>
				</div>
				<div id="tableRight" align="right">
					<ul style="list-stile:none;padding:0;margin:0">
						<li>Сохранить фразу</li><br>
						<li id="saves"></li>
						<li class="stats" id="addSaved" title="Фраза не сохранена" role="button"><i class="far fa-bookmark"></i></li><br><br>
						<li>Репорт</li><br>
						<li id="reportPhr" role="button"><img src="../../images/icons/report.svg" alt="репорт" title="Отправить репорт" width="30px"></li><br>
						<li id="approved"></li>
					</ul>
				</div>
			</div>
		</div>
		<img style="margin: 30px 5px" src="../../images/icons/phrase1.svg" alt="фраза:" width="15px">
		<p id="phraseStr"><em>-</em></p>
		
		<table id="infoTable" cellspacing="0" cellpadding="0">
			<tbody>
				<tr class="it-tags" hiddened>
					<td class="phr-th">Синонимы и тэги</td>
					<td></td>
				</tr>
				<tr class="it-tags" hiddened>
					<td class="phr-td">-
						<br><br>
					</td>
					<td></td>
				</tr>
				<tr>
					<td class="phr-th">Название видео</td>
					<td class="phr-th">Ссылка на видео</td>
				</tr>
				<tr valign="top">
					<td class="phr-td">-
						<br><br> <!-- !!! -->
					</td>
					<td class="phr-a">-
						<!-- <a href="" target="_blank"></a> -->
					</td>
				</tr>
			</tbody>
		</table>
		
		<section id="download1"><img src="../../images/icons/download.svg" alt="Файлы:" width="50px"></section>
		
		<details class="infoDtls" id="infoDtls1">
			<summary><span>Посмотреть сводку</span>&#8230;</summary>
			<div class="content"><br>
				<table border="1px solid black" cellspacing="0" cellpadding="10">
					<tbody>
						<tr valign="top">
							<td>
								<p class="phr-th">Фраза</p>
								<p class="phr-td1">-</p>
							</td>
							<td>
								<p class="phr-th">Авторы</p>
								<p class="phr-td1">-</p>
							</td>
						</tr>
						<tr valign="top">
							<td>
								<p class="phr-th">Синонимы и теги</p>
								<p class="phr-td1">-</p>
							</td>
							<td>
								<p class="phr-th">Тон</p>
								<p class="phr-td1">-</p>
							</td>
						</tr>
						<tr valign="top">
							<td>
								<p class="phr-th">Название видео</p>
								<p class="phr-td1">-</p>
							</td>
							<td>
								<p class="phr-th">Эмоции</p>
								<p class="phr-td1">-</p>
							</td>
						</tr>
						<tr valign="top">
							<td>
								<p class="phr-th">Идентификатор видео</p>
								<p class="phr-td1">-</p>
							</td>
							<td>
								<p class="phr-th">Начало</p>
								<p class="phr-td1">-</p>
							</td>
						</tr>
						<tr valign="top">
							<td>
								<p class="phr-th">Дата добавления</p>
								<p class="phr-td1">-</p>
							</td>
							<td>
								<p class="phr-th">Web-камера</p>
								<p class="phr-td1">-</p>
							</td>
						</tr>
						<tr valign="top">
							<td>
								<p class="phr-th">Пользователь</p>
								<p class="phr-td1">-</p>
							</td>
							<td>
								<p class="phr-th">Ресурсы для скачивания</p>
								<p class="phr-td1">-</p>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</details>
		
		<section id="phrHelp">
			<p>!</p>
			<div>
				<p>Сообщить о проблеме</p>
				<p>Видео отсутствует или недостоверная информация? Сообщи нам!</p>
			</div>
		</section>
		
		<section id="actions">
			<div>
				<p>
					<span><img style="padding-bottom: 2px;" class="stats" src="../../images/icons/hamburger1.svg" alt="" width="22px"></span>
					<span>Доступные действия</span>
				</p>
				<!-- <li id="addSaved" role="button"><span>Сохранить в закладки</span></li> -->
				<li><span><a href="<?php echo 'details?phrase='.$_GET['phrase']; ?>#edit" id="tryedit">Редактировать</a><span></li>
			</div>
			<div>
				<p>
					<span><img style="padding-bottom: 6px;" class="stats" src="../../images/icons/report.svg" alt="" width="30px"></span>
					<span>Связаться с нами</span>
				</p>
				<li id="suggestEdit" role="button"><span>Предложить исправление</span></li>
				<li id="complainPhr" role="button"><span>Пожаловаться</span></li>
			</div>
		</section>
	</main>
	
	<footer-component></footer-component>
	
	<script src="../../js/phrpquote.js" type="text/javascript"></script>

	<?php finalActions(); ?>
</body>
</html>