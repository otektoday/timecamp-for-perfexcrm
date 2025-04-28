<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="panel_s">
          <div class="panel-body">
            <h4 class="no-margin"><?php echo 'TimeCamp Integration Settings'; ?></h4>
            <hr class="hr-panel-heading" />
            <form method="post" action="<?php echo admin_url('timecamp_sync/settings'); ?>">
                <?php echo form_open(admin_url('timecamp_sync/settings')); ?>
              <div class="form-group">
                <label for="timecamp_api_key">TimeCamp API Key</label>
                <input type="text" class="form-control" name="timecamp_api_key" id="timecamp_api_key" value="<?php echo html_escape($api_key); ?>">
              </div>
              <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
