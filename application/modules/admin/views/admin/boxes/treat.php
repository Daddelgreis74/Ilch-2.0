<form class="form-horizontal" method="POST" action="">
    <?=$this->getTokenField() ?>
    <legend>
        <?php
        if ($this->get('box') != '') {
            echo $this->getTrans('editBox');
        } else {
            echo $this->getTrans('addBox');
        }
        ?>
    </legend>
    <div class="form-group">
        <label for="boxTitleInput" class="col-lg-2 control-label">
            <?=$this->getTrans('boxTitle') ?>:
        </label>
        <div class="col-lg-2">
            <input class="form-control"
                   type="text"
                   name="boxTitle"
                   id="boxTitleInput"
                   value="<?php if ($this->get('box') != '') { echo $this->escape($this->get('box')->getTitle()); } ?>" />
        </div>
    </div>
    <div class="form-group">
        <textarea class="form-control" id="ilch_html" name="boxContent"><?php if ($this->get('box') != '') { echo $this->get('box')->getContent(); } ?></textarea>
    </div>
    <?php if ($this->get('multilingual') && $this->getRequest()->getParam('locale') != ''): ?>
        <div class="form-group">
            <label for="boxLanguageInput" class="col-lg-2 control-label">
                <?=$this->getTrans('boxLanguage') ?>:
            </label>
            <div class="col-lg-2">
                <select class="form-control" name="boxLanguage" id="boxLanguageInput">
                    <?php
                    foreach ($this->get('languages') as $key => $value) {
                        $selected = '';

                        if ($key == $this->get('contentLanguage')) {
                            continue;
                        }

                        if ($this->getRequest()->getParam('locale') == $key) {
                            $selected = 'selected="selected"';
                        }

                        echo '<option '.$selected.' value="'.$key.'">'.$this->escape($value).'</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    <?php endif; ?>
    <?php if ($this->get('box') != ''): ?>
        <?=$this->getSaveBar('updateButtonBox') ?>
    <?php else: ?>
        <?=$this->getSaveBar('addButtonBox') ?>
    <?php endif; ?>
</form>

<script>
<?php
$boxID = '';

if ($this->get('box') != '') {
    $boxID = $this->get('box')->getId();
}
?>
$('#boxLanguageInput').change
(
    this,
    function () {
        top.location.href = '<?php echo $this->getUrl(array('id' => $boxID)); ?>/locale/'+$(this).val();
    }
);
</script>
