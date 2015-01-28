<?
echo "
var vaccineTableData = [
	[{xtype: 'label', text: '" . _('BCG') . "', ctCls: 'powerUpColumnHeader'},[{" . genExtWidget('bcgDt1','datefield',0) . "}],[{" . genExtWidget('bcgDose','numberfield',0) . "}]],
	[{xtype: 'label', text: '" . _('Polio') . "', ctCls: 'powerUpColumnHeader'},[{" . genExtWidget('polioDtD1','datefield',0) . "}],[{" . genExtWidget('polioDose','numberfield',0) . "}]],
	[{xtype: 'label', text: '" . _('DTPer') . "', ctCls: 'powerUpColumnHeader'},[{" . genExtWidget('dtperDtD1','datefield',0) . "}],[{" . genExtWidget('dtperDose','numberfield',0) . "}]],
	[{xtype: 'label', text: '" . _('Rougeole') . "', ctCls: 'powerUpColumnHeader'},[{" . genExtWidget('rougeoleDtD1','datefield',0) . "}],[{" . genExtWidget('rougeoleDose','numberfield',0) . "}]],
	[{xtype: 'label', text: '" . _('RR') . "', ctCls: 'powerUpColumnHeader'},[{" . genExtWidget('rrDtD1','datefield',0) . "}],[{" . genExtWidget('rrDose','numberfield',0) . "}]],
	[{xtype: 'label', text: '" . _('DT') . "', ctCls: 'powerUpColumnHeader'},[{" . genExtWidget('dtDtD1','datefield',0) . "}],[{" . genExtWidget('dtDose','numberfield',0) . "}]],
	[{xtype: 'label', text: '" . _('Hépatite B') . "', ctCls: 'powerUpColumnHeader'},[{" . genExtWidget('hepbDtD1','datefield',0) . "}],[{" . genExtWidget('hepbDose','numberfield',0) . "}]],
	[{xtype: 'label', text: '" . _('Pentavalent') . "', ctCls: 'powerUpColumnHeader'},[{" . genExtWidget('pentavDtD1','datefield',0) . "}],[{" . genExtWidget('pentavDose','numberfield',0) . "}]],
	[{
	xtype: 'fieldset', layout:'column',
	ctCls: 'powerUpColumnHeader',
	border: false,
	items: [
		{xtype: 'label', text: '" . _('Autre') . " : '},
		{" . genExtWidget('vOther1desc','textfield',0) . ", ctCls: 'powerUpOtherInput'}
	]},
	[{" . genExtWidget('vOther1DtD0','datefield',0) . "}],[{" . genExtWidget('vOther1Dose','numberfield',0) . "}]]
]; 
var supplementTableData = [
	[{xtype: 'label', text: '" . _('Vitamine A') . "', ctCls: 'powerUpColumnHeader'},
	[{" . genExtWidget('supplementVitA1','datefield',0) . "}]],
	[{xtype: 'label', text: '" . _('Fer.') . "', ctCls: 'powerUpColumnHeader'},
	[{" . genExtWidget('supplementIron1','datefield',0) . "}]],
	[{xtype: 'label', text: '" . _('Iode') . "', ctCls: 'powerUpColumnHeader'},
	[{" . genExtWidget('supplementIodine1','datefield',0) . "}]],
	[{xtype: 'label', text: '" . _('Déparasitage') . "', ctCls: 'powerUpColumnHeader'},
	[{" . genExtWidget('supplementDeworm1','datefield',0) . "}]],
	[{xtype: 'label', text: '" . _('Zinc') . "', ctCls: 'powerUpColumnHeader'},
	[{" . genExtWidget('supplementZinc1','datefield',0) . "}]],
	[{
	xtype: 'fieldset', layout:'column',
	ctCls: 'powerUpColumnHeader',
	border: false,
	items: [{xtype: 'label', text: '" . _('Autre') . " : '},
	{" . genExtWidget('sOther1desc','textfield',0) . ", ctCls: 'powerUpOtherInput'}]},
	[{" . genExtWidget('sOther1DtD0','datefield',0) . "}]]
];   
";

?>

var personalHistoryPreviousHospitalization = {
    layout: {
        type: 'hbox'
    },
    defaults: {
        margins: '0 3'
    },
    items: [{
            xtype: 'label',
            text: '<?=_('Hospitalisation Antérieure :')?>'
        },{
            boxLabel: '<?=_('Oui')?>',
            <?= genExtWidget('hosp', 'radio', 1); ?>
        },{
            boxLabel: '<?=_('Non')?>',
            <?= genExtWidget('hosp', 'radio', 2); ?>
        },{
            boxLabel: '<?=_('Inconnu')?>',
            <?= genExtWidget('hosp', 'radio', 4); ?>
        },{
            xtype: 'label',
            text: '<?=_('Si oui, cause :')?>'
        },{
            width: '20em',
            <?= genExtWidget('hospSpe', 'textfield', 0); ?>
        }]
};
buildValidation(72, makeTestAllOrNone(makeTestAnyNotBlank('hospSpe0'),
                                      makeTestAnyIsChecked('hosp1')));

function powerUp2ExtTable(tableTitle, tableData, padding, colCnt) {

	var extDataTableItems = [tableTitle,'<?=_('Date')?>'].map(function(label) {
		return {xtype:'label', text:label, ctCls: 'powerUpHeader'};
	 });
        
	if (colCnt == 3) {
		extDataTableItems = extDataTableItems.concat(['<?=_('Dose')?>'].map(function(label) {
			return {xtype:'label', text:label, ctCls: 'powerUpHeader'};
	 })); 
	}

    extDataTableItems = extDataTableItems.concat(tableData.map(function(rowData) {
 	var rowDataExt = [rowData[0]];
	rowDataExt = rowDataExt.concat(rowData[1].map(function(rowItem) {
		    rowItem.width = '10em';
		    return rowItem;
		})); 
	if (colCnt == 3) rowDataExt = rowDataExt.concat(rowData[2].map(function(rowItem) {
		    rowItem.width = '10em';
		    return rowItem;
		}));
	return rowDataExt;
    })); 

    return {
	xtype: 'panel',
	flex: 0,
	border: false,
	autoHeight: true,
	padding: '0 0 0 ' + padding,
	cls: 'powerUp',
        layout: {
            type: 'table',
            columns: colCnt,
            tableAttrs: {
                cls: 'table table-bordered power-up-table'
            }
        },
	items: extDataTableItems
    };
}

var powerUpVaccSupp = new Ext.FormPanel({
	title: '<?=_('VACCINATION ET SUPPLEMENTATION')?>',
	id: 'vaccSupp',
	border: false,
	padding: 5,
	autoHeight: true,
	autoWidth: true,
	autoScroll: true,
	items: [{ 
		layout: 'hbox',
		defaults: {
			style: {marginBottom: '0em'}
		},
		items: [
			powerUp2ExtTable('<?=_('Vaccin')?>', vaccineTableData, 0, 3),
			powerUp2ExtTable('<?=_('Supplément')?>', supplementTableData, 10, 2)
		]  
	},
		personalHistoryPreviousHospitalization
        ]
});
