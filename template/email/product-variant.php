<table style='margin-top:20px;margin-bottom:10px;margin-left:auto;margin-right:auto;width:480px;' cellpadding='5' cellspacing='0' border='1' bordercolor='#444444'>
    <tbody>
        <?php foreach($variants as $_variant) : ?>
        <tr>
            <td style='width:120px;font-weight:bold;'><?php echo ucfirst($_variant['type']); ?></td>
            <td><?php echo $_variant['label']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>