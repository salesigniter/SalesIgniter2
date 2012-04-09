<?php
if (isset($beforePageTitle)){
	echo $beforePageTitle;
}
if (isset($pageTitle) && !empty($pageTitle)){
	echo htmlBase::newElement('div')
		->addClass('ui-bar ui-bar-b pageHeading')
		->html('<h3>' . $pageTitle . '</h3>')
		->draw();
}
if (isset($afterPageTitle)){
	echo $afterPageTitle;
}
?>
<div class="ui-body ui-body-b">
	<?php
	if (isset($beforePageForm)){
	echo $beforePageForm;
	}
	if (isset($pageForm)){
	echo '<form name="' . $pageForm['name'] . '" action="' . $pageForm['action'] . '" method="' . $pageForm['method'] . '">' . "\n";
	}
	if (isset($beforePageContainer)){
	echo $beforePageContainer;
	}
	if (isset($beforePageContent)){
	echo $beforePageContent;
	}
	?>
	<div>
		<div>
			<?php
			echo $pageContent;
			?>
		</div>
		<?php
		if (isset($afterPageContent)){
		echo $afterPageContent;
		}
		?>
	</div>
	<?php
	if (isset($afterPageContainer)){
	echo $afterPageContainer;
	}
	if (isset($beforePageButtons)){
	echo $beforePageButtons;
	}
	if (isset($pageButtons) && !empty($pageButtons)){
	?>
	<div style="text-align:center;"><?php
	echo $pageButtons;
	?></div>
	<?php
	}
	if (isset($afterPageButtons)){
	echo $afterPageButtons;
	}

	if (isset($pageForm)){
	echo '</form>' . "\n";
	}
	if (isset($afterPageForm)){
	echo $afterPageForm;
	}
	?>
</div>
