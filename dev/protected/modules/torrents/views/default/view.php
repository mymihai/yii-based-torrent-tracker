<?php
/**
 * @var $this    DefaultController
 * @var $model   TorrentGroup
 * @var $torrent Torrent
 */
?>
<?php
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile(Yii::app()->getModule('torrents')->getAssetsUrl() . '/js/torrents.js');
$cs->registerCssFile(Yii::app()->getBaseUrl() . '/js/fancyapps-fancyBox/source/jquery.fancybox.css');
$cs->registerScriptFile(Yii::app()->getBaseUrl() . '/js/fancyapps-fancyBox/source/jquery.fancybox.js');
?>

	<h1><?php echo $model->getTitle() ?></h1>

	<div class="row-fluid">
	<div class="span3">
	<?php
	$img = CHtml::image($model->getImageUrl(290, 0), $model->getTitle());
	echo CHtml::link($img,
		$model->getImageUrl(),
		array(
		     'class' => 'fancybox img-polaroid torrentImage',
		     'rel'   => 'group'
		));
	?>
	<?php
	$this->widget('application.modules.torrents.widgets.TorrentGroupMenu',
		array(
		     'model' => $model
		));
	?>
	<?php $this->widget('application.modules.advertisement.widgets.AdsBlockWidget',
		array(
		     'systemName' => 'underTorrentImage',
		))
	?>
	</div>

	<div class="span9">

		<?php $this->widget('application.modules.advertisement.widgets.AdsBlockWidget',
			array(
			     'systemName' => 'topTorrentView',
			     'model'      => $model
			))
		?>

		<dl class="dl-horizontal torrentView">
			<?php
			foreach ( $model->getEavAttributesWithKeys() AS $name => $value ) {
				echo '<dt>' . $name . '</dt>';
				echo '<dd>' . $value . '</dd>';
			}
			?>

			<dt><?php echo Yii::t('categoryModule.common', 'Категория'); ?></dt>
			<dd><?php echo CHtml::link($model->category->getTitle(),
					array(
					     '/torrents/default/index',
					     'category[]' => $model->category->getTitle()
					)); ?></dd>

			<?php
			if ($model->getTags()) {
			?>
			<dt><?php echo Yii::t('tagsModule.common', 'Теги'); ?></dt>
						<dd>
				<?php
				$tags = '';
				foreach ( $model->getTags() AS $key => $tag ) {
					$tags .= ($tags ? ', ' : '') . CHtml::link($tag,
							array(
							     '/torrents/default/index',
							     'tags' => $tag
							));
				}
				echo $tags . '</dd>';
				}
				?>


		<dt><?php echo Yii::t('ratingsModule.common', 'Рейтинг'); ?></dt>
		<dd>
			<?php $this->widget('application.modules.ratings.widgets.TorrentGroupRating',
				array(
				     'model' => $model,
				)); ?>
		</dd>

		</dl>

	<div class="accordion torrentsList">

<?php foreach ( $model->torrents(array('order' => 'ctime DESC')) AS $key => $torrent ) { ?>

	<div class="accordion-group">
	<div class="accordion-heading" id="torrent<?php echo $torrent->getId() ?>">
	<?php echo CHtml::link('<i class="icon-download"></i>',
		array(
		     '/torrents/default/download',
		     'id' => $torrent->getId()
		),
		array(
		     'class'       => 'btn btn-mini',
		     'data-toggle' => 'tooltip',
		     'title'       => Yii::t('torrentsModule',
			     'Скачать {torrentName}',
			     array(
			          '{torrentName}' => $torrent->getTitle()
			     ))
		)) ?>

		<?php if ( Yii::app()->getUser()->checkAccess('reports.default.create') ) { ?>

			<a href="<?php echo Yii::app()->createUrl('/reports/default/create/'); ?>" data-model="<?php echo $torrent->resolveClassName(); ?>" data-id="<?php echo $torrent->getId(); ?>" data-action="report" class="btn btn-mini" data-toggle="tooltip" data-placement="top" title="<?php echo Yii::t('reportsModule.common',
				'Пожаловаться на {torrentName}',
				array(
				     '{torrentName}' => $torrent->getTitle()
				)); ?>"><i class="icon-warning-sign"></i></a>

		<?php } ?>

		<a href="#" class="btn btn-mini" data-comments-for="<?php echo $torrent->getId() ?>" data-toggle="tooltip" data-placement="top" title="<?php echo Yii::t('torrentsModule.common',
			'Смотреть комментарии только для {torrentName}',
			array(
			     '{torrentName}' => $torrent->getTitle()
			)); ?>"><i class="icon-comment"></i></a>

		<a href="<?php echo Yii::app()->createUrl('/torrents/default/fileList') ?>" class="btn btn-mini" data-action="fileList" data-id="<?php echo $torrent->getId(); ?>" data-toggle="tooltip" data-placement="top" title="<?php echo Yii::t('torrentsModule.common',
			'Смотреть список файлов для {torrentName}',
			array(
			     '{torrentName}' => $torrent->getTitle()
			)); ?>"><i class="icon-file"></i></a>

		<?php
		if ( Yii::app()->getUser()->checkAccess('torrentsUpdate') ) {
			?>
			<a href="<?php echo Yii::app()->createUrl('/torrents/default/updateTorrent/',
				array(
				     'id' => $torrent->getId()
				)); ?>" class="btn btn-mini" data-toggle="tooltip" data-placement="top" title="<?php echo Yii::t('torrentsModule.common',
				'Редактировать {torrentName}',
				array(
				     '{torrentName}' => $torrent->getTitle()
				)); ?>"><i class="icon-edit"></i></a>
		<?php
		}
		if ( Yii::app()->getUser()->checkAccess('torrents.default.deleteTorrent') ) {
			?>
			<a href="<?php echo Yii::app()->createUrl('/torrents/default/deleteTorrent',
				array('id' => $torrent->getId())) ?>" class="btn btn-mini torrentDelete" data-toggle="tooltip" data-placement="top" title="<?php echo Yii::t('torrentsModule.common',
				'Удалить торрент {torrentName}',
				array(
				     '{torrentName}' => $torrent->getTitle()
				)); ?>"><i class="icon-trash"></i></a>
		<?php } ?>

		<a class="accordion-toggle" data-toggle="collapse" href="#collapse<?php echo $torrent->getId() ?>"><?php echo $torrent->getSeparateAttribute() ?></a>

		<span class="divider-vertical">|</span>

		<span><abbr title="<?php echo Yii::t('torrentsModule.common',
				'Размер: {size} bytes',
				array('{size}' => $torrent->getSize())); ?>"><?php echo $torrent->getSize(true); ?></abbr></span>

		<span class="divider-vertical">|</span>

		<span><abbr title="<?php echo Yii::t('torrentsModule.common',
				'Добавлено: {date}',
				array(
				     '{date}' => $torrent->getCtime('d.m.Y H:i')
				)) ?>"><?php echo TimeHelper::timeAgoInWords($torrent->getCtime()); ?></abbr></span>

		<span class="divider-vertical">|</span>

		<span><i class="icon-upload" data-toggle="tooltip" data-placement="top" title="<?php echo $torrent->getAttributeLabel('seeders') ?>"></i> <?php echo $torrent->getSeeders(); ?>
			<i class="icon-download" data-toggle="tooltip" data-placement="top" title="<?php echo $torrent->getAttributeLabel('leechers') ?>"></i> <?php echo $torrent->getLeechers(); ?></span>

		<span class="divider-vertical">|</span>

		<span><?php echo $torrent->getAttributeLabel('downloads') ?>
			: <?php echo $torrent->getDownloads(); ?></span>
		</div>

		<div id="collapse<?php echo $torrent->getId() ?>" class="accordion-body collapse">
                <div class="accordion-inner">
                    <dl class="dl-horizontal">
	                    <?php
	                    foreach ( $torrent->getEavAttributesWithKeys() AS $name => $value ) {
		                    echo '<dt>' . $name . '</dt>';
		                    echo '<dd>' . $value . '</dd>';
	                    }
	                    ?>
                    </dl>
                </div>
            </div>
		<div id="fileList<?php echo $torrent->getId() ?>" class="accordion-body collapse">
                <div class="accordion-inner"></div>
            </div>
		</div>
<?php } ?>
	</div>

		<?php $this->widget('application.modules.comments.widgets.CommentsTreeWidget',
			array(
			     'model' => $model,
			)); ?>

		<?php $this->widget('application.modules.comments.widgets.AnswerWidget',
			array(
			     'model'    => $model,
			     'torrents' => $model->torrents
			)); ?>
	</div>

	</div>

<?php $this->widget('application.modules.reports.widgets.ReportModal'); ?>