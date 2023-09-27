export const rtViewTabulatorOptions = {
	height: "100%",
	layout: 'fitColumns',
	selectable: true,
	placeholder: "Keine Daten verfügbar",
	headerFilter: true,
	columns: [
		{title: 'Prestudent ID', field: 'PrestudentID',headerFilter: true, headerSort: true},
		{title: 'Vorname', field: 'Vorname', headerFilter: true},
		{title: 'Nachname', field: 'Nachname', headerFilter: true},
		{title: 'Geburtsdatum', field: 'Geburtsdatum', headerFilter: true},
		{title: 'Geschlecht', field: 'Geschlecht', headerFilter: true},
		{title: 'Studiengang', field: 'Studiengang', headerFilter: true},
		{title: 'OrgForm', field: 'OrgForm', headerFilter: true},
		{title: 'Sem', field: 'Sem', headerFilter: true, visible: false},
		{title: 'RT-Punkte', field: 'RT-Punkte', headerFilter: true, visible: false},
		{title: 'RT-Datum', field: 'RT-Datum', headerFilter: true, visible: false},
		{title: 'Status', field: 'Status', headerFilter: true, visible: false},
		{title: 'Status Datum', field: 'Status Datum', headerFilter: true, visible: false},
		{title: 'Status andere', field: 'Status andere', headerFilter: true, visible: false},
		{title: 'Prio', field: 'Prio', headerFilter: true, visible: false},
		{title: '∑ Bakk', field: 'Summe_Bakk', headerFilter: true, visible: false},
		{title: '∑ Bakk aktiv', field: 'Summe_Bakk_aktiv', headerFilter: true, visible: false},
		{title: 'Letzter in Kette', field: 'Letzter in Kette', headerFilter: true, visible: false},
		{title: '∑ Aufgenommene', field: 'Summe_Aufgenommene', headerFilter: true, visible: false},
		{title: 'Alter der ZGV', field: 'Alter der ZGV', headerFilter: true, visible: false},
		{title: 'Anzahlung', field: 'Anzahlung', headerFilter: true, visible: false}
	],
};

export const rtViewerTabulatorEventHandlers = [
	{
		event: "rowClick",
		/*handler: function(e, row) {
			alert(row.getData().Data);
		}*/
	}
];
