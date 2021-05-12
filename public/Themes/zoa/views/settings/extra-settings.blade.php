
<div class="data-head d-flex w-100 align-items-center justify-content-between mb-5 mt-5">
   <div>
      <button class="btn btn-primary c-save ml-auto d-none d-md-block"><span><?= __('Save') ?></span> <em class="icon ni ni-edit"></em></button>
   </div>
</div>
<div class="row">
  <div class="col-md-6">
      <div class="form-group mt-5 custom">
        <label class="muted-deep fw-normal form-label fw-normal ml-2 mb-4">
          <span><?= __('Top Product') ?></span>
          <small class="d-block mt-2"><?= __('Show 3 most purchased product on your store home page') ?></small>
        </label>
        <select name="theme_extra[top_product]" class="form-select" data-search="off" data-ui="lg">
          <option value="0" <?= user('extra.top_product') == 0 ? 'selected' : '' ?>><?= __('Hide') ?></option>
          <option value="1" <?= user('extra.top_product') == 1 ? 'selected' : '' ?>><?= __('Show') ?></option>
        </select>
     </div>
  </div>
</div>