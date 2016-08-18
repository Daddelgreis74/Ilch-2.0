<?php
// This is needed to avoid "Can't use method return value in write context". See PHP documentation for further information.
$code = $this->getRequest()->getParam('code');
$confirm = $this->get('confirmed');

if (empty($code) || empty($confirm)): ?>
    <legend><?=$this->getTrans('unlockUserAcc'); ?></legend>
    <form class="form-horizontal" method="POST" action="<?=$this->getUrl(['action' => $this->getRequest()->getActionName()]) ?>">
        <?=$this->getTokenField() ?>
        <?php $errors = $this->get('errors'); ?>
        <div class="form-group <?php if (!empty($errors['confirmedCode'])) { echo 'has-error'; }; ?>">
            <label for="confirmedCode" class="control-label col-lg-2">
                <?=$this->getTrans('confirmCode') ?>:
            </label>
            <div class="col-lg-8">
                <input type="text"
                       class="form-control"
                       name="confirmedCode"
                       id="confirmedCode"
                       value="" />
                <?php if (!empty($errors['confirmedCode'])): ?>
                    <span class="help-inline"><?=$this->getTrans($errors['confirmedCode']) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-lg-10" align="right">
            <?=$this->getSaveBar('menuConfirm', 'Confirm') ?>
        </div>
    </form>
<?php else: ?>
    <div class="row">
        <div class="col-lg-2 fa-4x check">
            <i class="fa fa-check-circle text-success" title=""></i>
        </div>
        <div class="col-lg-10">
            <?=$this->getTrans('accountApproved') ?>
        </div>
    </div>
<?php endif; ?>
