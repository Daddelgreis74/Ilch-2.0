<?php 
$receivers = $this->get('receivers');
if(!empty($receivers)){ ?>
<form method="POST" class="form-horizontal" action="<?php echo $this->getUrl(array('action' => $this->getRequest()->getActionName())); ?>">
    <?php echo $this->getTokenField(); ?>
    <div class="form-group">
        <label for="receiver" class="col-lg-2 control-label">
            <?php echo $this->getTrans('receiver'); ?>:
        </label>
        <div class="col-lg-8">
            <select id="receiver"
                    class="form-control"
                    name="contact_receiver">
                <?php
                    foreach($receivers as $receiver)
                    {
                        echo '<option value="'.$receiver->getId().'">'.$this->escape($receiver->getName()).'</option>';
                    }
                ?>
            </select>
        </div>
    </div>
    <div class="form-group">
        <label for="name" class="col-lg-2 control-label">
            <?php echo $this->getTrans('name'); ?>:
        </label>
        <div class="col-lg-8">
            <input id="name"
                   class="form-control"
                   name="contact_name"
                   type="text"
                   value="" />
        </div>
    </div>
    <div class="form-group">
        <label for="email" class="col-lg-2 control-label">
            <?php echo $this->getTrans('email'); ?>:
        </label>
        <div class="col-lg-8">
            <input id="email"
                   class="form-control"
                   name="contact_email"
                   type="text"
                   value="" />
        </div>
    </div>
    <div class="form-group">
        <label for="message" class="col-lg-2 control-label">
            <?php echo $this->getTrans('message'); ?>:
        </label>
        <div class="col-lg-8">
            <textarea id="message"
                      class="form-control"
                   name="contact_message"></textarea>
        </div>
    </div>
    <div class="form-group">
        <label class="col-lg-2 control-label">
            <?=$this->getTrans('captcha'); ?>
        </label>
        <div class="col-lg-8">
            <?=$this->getCaptchaField();?>
            <input type="text"
                  id="captcha-form"
                  class="form-control"
                  autocomplete="off"
                  name="captcha" />
            <a href="#" onclick="
                document.getElementById('captcha').src='<?php $this->getUrl()?>/application/libraries/Captcha/Captcha.php?'+Math.random();
                document.getElementById('captcha-form').focus();"
                id="change-image"><?php echo $this->getTrans('captchaRead'); ?></a>
        </div>
    </div>
    <div class="form-group">
        <div class="col-lg-offset-2 col-lg-8">
            <button type="submit" class="btn" name="save">
                <?php echo $this->getTrans('send'); ?>
            </button>
        </div>
    </div>
</form>
<?php } ?>
