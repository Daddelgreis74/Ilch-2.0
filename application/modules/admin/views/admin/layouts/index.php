<form class="form-horizontal" method="POST" action="<?php echo $this->getUrl(array('action' => $this->getRequest()->getActionName())); ?>">
    <?php echo $this->getTokenField(); ?>
    <legend><?php echo $this->getTrans('layouts'); ?></legend>
        <?php
            $i = 0;
            foreach ($this->get('layouts') as $layout) {
                if ($i !== 0 && $i % 3 == 0) {
                    echo '</div><br />';
                }

                if ($i % 3 == 0) {
                    echo '<div class="row">';
                }
        ?>
            <div class="col-lg-4">
                <div class="thumbnail">
                    <img src="<?php echo $this->getStaticUrl('../application/layouts/'.$layout->getKey().'/config/screen.png'); ?>" />
                    <div class="caption">
                        <h3><?php echo $this->escape($layout->getKey()); ?></h3>
                        <p><?php echo $this->escape($layout->getDesc()); ?></p>
                        <p>
                            <a title="<?php if ($this->get('defaultLayout') == $layout->getKey()) { echo $this->getTrans('isDefault'); } else { echo $this->getTrans('setDefault'); } ?>"
                                href="<?php echo $this->getUrl(array('action' => 'default', 'key' => $layout->getKey())); ?>">
                                <?php
                                if ($this->get('defaultLayout') == $layout->getKey()) {
                                    echo '<i class="fa fa-check-square-o fa-2x text-success"></i>';
                                } else {
                                    echo '<i class="fa fa-square-o fa-2x"></i>';
                                }
                                ?>
                            </a>
                            <?php if($layout->getModulekey() != '') { ?>
                            <a class="fa-2x" href="<?php echo $this->getUrl(array('module' => $layout->getModulekey(),'controller' => 'index', 'action' => 'index')); ?>">
                                <i class="fa fa fa-cogs"></i> Settings
                            </a>
                            <?php } ?>
                            <span class="deleteLayout clickable fa fa-trash-o fa-2x text-danger pull-right"
                              data-clickurl="<?php echo $this->getUrl(array('action' => 'delete', 'key' => $layout->getKey())); ?>"
                              data-toggle="modal"
                              data-target="#deleteModal"
                              data-modaltext="<?php echo $this->escape($this->getTrans('askIfDeleteLayout', $layout->getKey())); ?>"
                              title="<?php echo $this->getTrans('delete'); ?>">
                            </span>
                        </p>
                    </div>
                </div>
            </div>
            
        <?php
            $i++;
        }
        ?>
        </div>
    </div>
</form>
<script>
    $('.deleteLayout').on('click', function(event) {
        $('#modalButton').data('clickurl', $(this).data('clickurl'));
        $('#modalText').html($(this).data('modaltext'));
    });

    $('#modalButton').on('click', function(event) {
        window.location = $(this).data('clickurl');
    });
</script>