<?php
global $sejoli;

if(isset($sejoli['messages'])) :
    foreach($sejoli['messages'] as $type => $messages) :
        if(is_array($messages) && 0 < count($messages)) :
        ?>
        <div class="ui message <?= $type; ?>">
            <ul class='list'>
                <?php foreach($messages as $message) : ?>
                <li><?php echo $message; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        endif;
    endforeach;
endif;
?>
