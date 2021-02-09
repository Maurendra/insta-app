window.onload = function() {
	var interval = setInterval(getLasData, 4500);
	function getLasData() {
		$.ajax({
			url: base_url_api+"dataBaru",
			method: "POST",
			data: {
				lastDate: $("#lastDate").val()
			},
			success: function( result ) {
				if(result.dataBaru === 1) {
					$("#lastDate").val(result.lastDate);
					tambahWilayahInterval();
				}
			}
		});
	}
	
	var label_bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
	var option_scales = {
    xAxes: [{
      gridLines: {
        display:false,
        drawBorder: false
      }
    }],
    yAxes: [{
      gridLines: {
        display:true,
        borderDash: [2, 2],
        color: "#EAEDFD",
        drawBorder: false
      },
			ticks: {
				beginAtZero: true
			}
    }]
	};
	var legend = {
		position: 'bottom',
	};
	var layout = {
    padding: {
      left: 10,
      right: 10,
      top: 0,
      bottom: 0
    }
  };

	// Bar 1
	var bar1ChartData = {
		labels: label_bulan,
		datasets: datasetsBar1
	};
	var ctx = document.getElementById('Bar1').getContext('2d');
	window.Bar1 = new Chart(ctx, {
		type: 'bar',
		data: bar1ChartData,
		options: {
			cornerRadius: 10,
			responsive: true,
			aspectRatio: 1.85,
			legend: legend,
	    layout: layout,
			title: {
				display: true,
				text: ['Jumlah Tenaga Kerja',tahun]
			},
			scales: option_scales
		}
	});

	// Bar 2
	var bar2ChartData = {
		labels: label_bulan,
		datasets: datasetsBar2
	};
	var ctx = document.getElementById('Bar2').getContext('2d');
	window.Bar2 = new Chart(ctx, {
		type: 'bar',
		data: bar2ChartData,
		options: {
			cornerRadius: 10,
			responsive: true,
			aspectRatio: 1.85,
			legend: legend,
	    layout: layout,
			title: {
				display: true,
				text: ['Total Pajak yang diterima',tahun]
			},
			scales: option_scales
		}
	});

	// Bar 3
	var bar3ChartData = {
		labels: label_bulan,
		datasets: datasetsBar3
	};
	var ctx = document.getElementById('Bar3').getContext('2d');
	window.Bar3 = new Chart(ctx, {
		type: 'bar',
		data: bar3ChartData,
		options: {
			cornerRadius: 5,
			responsive: true,
			aspectRatio: 1.85,
			legend: legend,
	    layout: layout,
			title: {
				display: true,
				text: ['Total Gross Income',tahun]
			},
			scales: option_scales
		}
	});

	// Pie4
	var pie4ChartData = datasetsPie4;
	var ctx = document.getElementById('Pie4').getContext('2d');
	window.Pie4 = new Chart(ctx, {
		type: 'doughnut',
		data: pie4ChartData,
		options: {
			responsive: true,
			aspectRatio: 1.85,
			legend: {
				position: 'right',
			},
			title: {
				display: true,
				text: 'Jumlah Perusahaan'
			}
		}
	});

	// Bar 5
	var bar5ChartData = {
		labels: label_bulan,
		datasets: datasetsBar5
	};
	var ctx = document.getElementById('Bar5').getContext('2d');
	window.Bar5 = new Chart(ctx, {
		type: 'bar',
		data: bar5ChartData,
		options: {
			cornerRadius: 10,
			responsive: true,
			aspectRatio: 1.85,
			legend: legend,
	    layout: layout,
			title: {
				display: true,
				text: ['Total BPJS Kesehatan',tahun]
			},
			scales: option_scales
		}
	});

	// Bar 6
	var bar6ChartData = {
		labels: label_bulan,
		datasets: datasetsBar6
	};
	var ctx = document.getElementById('Bar6').getContext('2d');
	window.Bar6 = new Chart(ctx, {
		type: 'bar',
		data: bar6ChartData,
		options: {
			cornerRadius: 10,
			responsive: true,
			aspectRatio: 1.85,
			legend: legend,
	    layout: layout,
			title: {
				display: true,
				text: ['Total BPJS Ketenagakerjaan',tahun]
			},
			scales: option_scales
		}
	});

};