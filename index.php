<?php

if ($_POST['submit']) {
	$lignes[] = $_POST['hidden'];
	$chemin = 'fichier.csv';
	$fichier_csv = fopen($chemin, 'a+');
	fprintf($fichier_csv, chr(0xEF).chr(0xBB).chr(0xBF));
	fputcsv($fichier_csv, $lignes);
	var_dump($lignes);
	fclose($fichier_csv);
}

class Champs {

	var $name;
	var $code;
	var $created;
	var $modified;
	var $deleted;

	function __construct($code, $name) {
		$this->code = $code;
		$this->name = $name;
		$this->created = time();
		$this->modified = time();
	}
}

class Modifier {

	var $name;
	var $code;
	var $created;
	var $modified;
	var $deleted;

	function __construct($code, $name) {
		$this->code = $code;
		$this->name = $name;
		$this->created = time();
		$this->modified = time();
	}
}

class ListeChamps {

	var $TabListeChamps = array();

	function __construct() {
	}

	function addChamps(Champs $Champs) {
		$this->TabListeChamps[$Champs->code] = $Champs;
	}
}

class ListeModifier {

	var $TabListeModifier = array();

	function __construct() {
	}

	function addModifier(Modifier $Modifier) {
		$this->TabListeModifier[$Modifier->code] = $Modifier;
	}
}

$ListeChamps = new ListeChamps();
$ListeChamps->addChamps(new Champs('id_produit','Id Produit'));
$ListeChamps->addChamps(new Champs('description','Déscription'));
$ListeChamps->addChamps(new Champs('date_creation','Date de création'));
$ListeChamps->addChamps(new Champs('prix','Prix'));
$ListeChamps->addChamps(new Champs('promotion','Promotion'));
$ListeChamps->addChamps(new Champs('description_longue','Déscription longue'));
$ListeChamps->addChamps(new Champs('categorie','Catégorie'));

$json_outputChamps = json_encode($ListeChamps->TabListeChamps, JSON_PRETTY_PRINT);

$ListeModifier = new ListeModifier();
$ListeModifier->addModifier(new Modifier('upper','Upper'));
$ListeModifier->addModifier(new Modifier('lower','Lower'));
$ListeModifier->addModifier(new Modifier('trim','Trim'));

$json_outputModifier = json_encode($ListeModifier->TabListeModifier, JSON_PRETTY_PRINT);
?>

<!DOCTYPE html>
<html>
<head>
	<title>draggableFinal</title>
	<meta charset="utf-8">
	<link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
	<form method="POST" action="index.php" id="form">
		<div id="contener" class="cf">
			<div id="colonne-liste-champs" class="sortable"></div>
			<div id="colonne-choix" class="sortable"></div>
			<div id="colonne-liste-modifier" class="sortableModifier"></div>
			<input type="hidden" id="hidden" name="hidden" value="">
			<input type="submit" value="valider" id="submit" name="submit" />
		</div>
	</form>
</body>
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<script>
	console.log('start');
	<?php if ($json_outputChamps != "" && $json_outputModifier != "") : ?>
		var liste_champs = <?=$json_outputChamps?>;
		var liste_modifier = <?=$json_outputModifier?>;
		console.log(liste_champs);
		console.log(liste_modifier);
		$(document).ready(function() {
			buildColumnsChamps();
			buildColumnsModifier();

			$("#colonne-choix, #colonne-liste-champs").sortable({
				connectWith: ".sortable",
				// containment: '#contener',
				remove: function(event, ui) {
					$('#colonne-liste-champs .Champ .Modifier').remove();
				}
			});

			$(".Modifier").draggable({
				helper: 'clone',
				// containment : "#contener",
				revert: 'invalid',
			});

			$("#colonne-choix").droppable({
				accept: ":not(.Modifier)",
				hoverClass: "hover",
				drop: function(event, ui) {
					var target = $(event.target);
					var dropped = $(ui.draggable).appendTo(target);
					if (dropped.hasClass('Champ')) {
						dropped.droppable({
							accept: ":not(.ui-sortable-helper)",
							hoverClass: "hover",
							greedy: true,
							drop: function (event, ui) {
								var target = $(event.target);
								$(ui.draggable).clone().appendTo(target);
								var currentItem = ui.draggable.attr('id');
								var destinationItem = $(this).attr('id');
								$($('#'+destinationItem+' .Modifier')).each(function(){
									var element = $(this).attr('id');
									var count = $('#'+destinationItem+' #'+currentItem).length;
									if ($('#'+element).attr('id') == $('#'+currentItem).attr('id') && count > 1) {
										$('#'+destinationItem+' #'+currentItem).not(':first').remove();
									}
								});
							}
						}).sortable({
							connectWith: '.Champ',
							containment : "#contener",
							receive: function(event, ui) {
								var currentItem = ui.item.attr('id');
								console.log('currentItem');
								console.log(currentItem);
								var destinationItem = ui.item.parent().attr('id');
								console.log('destinationItem');
								console.log(destinationItem);
								$('#'+destinationItem+' .Modifier').each(function(){
									var element = $(this).attr('id');
									var count = $('#'+destinationItem+' #'+currentItem).length;
									if ($('#'+element).attr('id') == $('#'+currentItem).attr('id') && count >= 1) {
										$('#'+destinationItem+' #'+currentItem).not(':first').remove();
									}
								});
							}
						});
					}
				}
			});
		})
		function buildColumnsChamps() {
			console.log('start buildColumnsChamps');
			for (var key in liste_champs) {
				console.log("element "+key);
				var data = liste_champs[key];
				var $div = $("<div class='Champ' code='"+data.code+"' id='"+data.code+"'>"+data.name+"</div>");
				$("#colonne-liste-champs").append($div);
			}
		}
		function buildColumnsModifier() {
			console.log('start buildColumnsModifier');
			for (var key in liste_modifier) {
				console.log("element "+key);
				var data = liste_modifier[key];
				var $div = $("<div class='Modifier' code='"+data.code+"' id='"+data.code+"'>"+data.name+"</div>");
				$("#colonne-liste-modifier").append($div);
			}
		}
		$("#submit").click(function(event) {
			// event.preventDefault();
			var jsonArray = [];
			$('#colonne-choix .Champ').each(function() {
				var champs = {};
				champs['code'] = $(this).attr('code');
				var modifierArray = [];
				$('.Modifier', $(this)).each(function() {
					var modifier = {};
					modifier['code'] = $(this).attr('code');
					modifierArray.push(modifier);
				});
				champs['modifier'] = modifierArray;
				jsonArray.push(champs);
			});
			console.log(jsonArray);
			// return false;
			var jsonArray = JSON.stringify(jsonArray, null, 4);
			$('#hidden').val(jsonArray);
		});
	<?php endif; ?>
</script>
</html>