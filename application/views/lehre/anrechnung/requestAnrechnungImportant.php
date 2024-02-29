<div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
    <!--    Beantragung: Fristen panel -->
    
<!-- start of accordion -->
    <div class="accordion" id="accordionExample">
  <div class="accordion-item">
    <h2 class="accordion-header">
        <button style="color:#0c63e4; background-color:#e7f1ff" class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
            <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoFristenTitle'); ?>
        </button>
    </h2>
    <div id="collapseOne" class="accordion-collapse collapse show" data-bs-parent="#accordionExample">
      <div class="accordion-body">
        <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoFristenBody'); ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
        <button style="color:#0c63e4; background-color:#e7f1ff" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
            <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
            <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoNachweisdokumenteTitle'); ?>
        </button>
    </h2>
    <div id="collapseTwo" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
        <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoNachweisdokumenteBody'); ?>
      </div>
    </div>
  </div>
  <div class="accordion-item">
    <h2 class="accordion-header">
        <button style="color:#0c63e4; background-color:#e7f1ff" class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
            <i class="fa fa-lg fa-info-circle" aria-hidden="true"></i>&ensp;
            <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoHerkunftKenntnisseTitle'); ?>
        </button>
    </h2>
    <div id="collapseThree" class="accordion-collapse collapse" data-bs-parent="#accordionExample">
      <div class="accordion-body">
        <?php echo $this->p->t('anrechnung', 'requestAnrechnungInfoHerkunftKenntnisseBody'); ?>
      </div>
    </div>
  </div>

</div>
<!-- end of accordion -->
