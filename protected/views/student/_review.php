<?php
/* @var $this StudentController */
/* @var $data Review */
?>

<table class="table table-hover">
	<tr>
		<td>
		<i class="icon icon-pencil"></i> Meninjau berkas 
		<?php echo CHtml::link(CHtml::encode($data->note->title), array('note/view', 'id'=>$data->note->id)); ?> 
		pada tanggal 
		<?php echo Yii::app()->format->datetime($data->timestamp); ?>
		</td>
	</tr>
</table>