
<script type="text/javascript">
    jQuery(function ($) {
	  var provider_fields = {
<?php
$providerlist = $this->getProviderlist();
foreach ($providerlist as $provider_) {
    $provider_name	 = $provider_[0];
    $provider		 = $this->getProvider($provider_name);
    $provider_fields	 = $provider->getFields();
    $field_		 = '[';
    foreach ($provider_fields as & $field) {
	  $field = '"' . $field . '"';
    }
    $provider_fields = implode(',', $provider_fields);
    $field_ .= $provider_fields;
    $field_ .= '],';
    echo '"' . $provider_name . '": ' . $field_;
}
?>
	  };
	  $(document).on('change', '.select_provider', function (e) {
		toggleField(provider_fields);
	  });
	  toggleField(provider_fields, 1);
    });
    function toggleField(provider_fields, norem) {
	  var provider_name = jQuery('.select_provider').find('option:selected').attr('value');
	  var provider = provider_fields[provider_name];
	  var $totoggle = jQuery('.totoggle');
	  $totoggle.hide();
	  jQuery.each(provider, function (i, val) {
		jQuery('.' + val).show();
	  });
	  if (!norem) {
		jQuery('#sc_tp_date').val('');
		jQuery('#sc_tp_city').val('');
		jQuery('#sc_tp_name').val('');
	  }
    }
</script>

<div class="sc_vm_trackintpost">
    <table class="table adminlist">
	  <tr>
		<th scope="col">Трекинг посылки</th>
	  </tr>
	  <tr>
		<td>
		    <?php
		    $providerlist	 = $this->genOption($providerlist);
		    $attribs		 = 'class="select_provider"';

		    $tracking	 = $this->getTracking($order_id);
		    $selected	 = $tracing->provider;
		    $tracknumber = $tracing->tracknumber;
		    $date		 = $tracing->date;
		    $city		 = $tracing->city;
		    $name		 = $tracing->name;
		    echo JHTML::_('select.genericlist', $providerlist, 'sc_tp_provider', $attribs, 'value', 'text', $selected);
		    ?>

		    <label for="sc_tp_tracknumber">ТекингНомер:</label>
		    <input type="text" value="<?= $tracknumber ?>" id="sc_tp_tracknumber" name="sc_tp_tracknumber" class="tracknumber"  placeholder="ТекингНомер">
		    <?php /* echo JHTML::_('calendar', $date, 'sc_tp_date', 'sc_tp_date', '%Y-%m-%d', 'class="date totoggle" placeholder="Дата"'); */?>
		    <input type="text" value="<?= $city ?>" id="sc_tp_city" name="sc_tp_city" class="city totoggle" placeholder="Город">
		    <input type="text" value="<?= $name ?>" id="sc_tp_name" name="sc_tp_name" class="name totoggle" placeholder="Название">

		    <input type="button" value="Установить" class="btn provider_submit" id="provider_submit">

		    <input type="hidden" value="<?= $order_id ?>" name="sc_tp_order_id" id="sc_tp_order_id">

		</td>
	  </tr>
    </table>
</div>



