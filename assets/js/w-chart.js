var MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
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
					tampilkan();
				}
			}
		});
	}
	
	// ==============================================================
	// Total Pajak yang Diterima
	// ==============================================================

	var canvas1 = document.getElementById('chart1').getContext('2d');
	window.chart1 = new Chart(canvas1, {
		type: 'bar',
		data: {
			labels: this.MONTHS,
			datasets: [{
				label: 'Dalam Jutaan',
				data: dataChart1,
				backgroundColor: warnaChart1
			}]
		},
		options: {
			cornerRadius: 10,
			responsive: true,
			legend: {
				position: 'bottom',
			},
			scales: option_scales
		}
	});

	// ==============================================================
	// Jumlah Tenaga Kerja
	// ==============================================================

	var canvas2 = document.getElementById('chart2').getContext('2d');
	window.chart2 = new Chart(canvas2, {
		type: 'bar',
		data: {
			labels: this.MONTHS,
			datasets: [{
				label: 'Pria',
				data: dataChart2_pria,
				backgroundColor: warnaChart2_pria
			}, {
				label: 'Wanita',
				data: dataChart2_wanita,
				backgroundColor: warnaChart2_wanita
			}]
		},
		options: {
			cornerRadius: 10,
			responsive: true,
			legend: {
				position: 'bottom',
			},
			scales: option_scales
		}
	});

	// ==============================================================
	// Total Penghasilan dan Pajak Karyawan
	// ==============================================================

	var canvas3 = document.getElementById('chart3').getContext('2d');
	window.chart3 = new Chart(canvas3, {
		type: 'bar',
		data: {
			labels: this.MONTHS,
			datasets: [{
				label: 'Gross Income (dalam jutaan)',
				data: dataChart3_gross,
				backgroundColor: warnaChart3_gross
			}, {
				label: 'PPH21 (dalam ratusan ribu)',
				data: dataChart3_pph21,
				backgroundColor: warnaChart3_pph21
			}, {
				label: 'Rasio (persentase)',
				data: dataChart3_rasio,
				backgroundColor: warnaChart3_rasio
			}]
		},
		options: {
			cornerRadius: 10,
			responsive: true,
			legend: {
				position: 'bottom',
			},
			scales: option_scales
		}
	});

	// ==============================================================
	// Penghasilan Rata-rata Karyawan
	// ==============================================================

	var canvas4 = document.getElementById('chart4').getContext('2d');
	window.chart4 = new Chart(canvas4, {
		type: 'bar',
		data: {
			labels: this.MONTHS,
			datasets: [{
				label: 'Gross Income (dalam jutaan)',
				data: dataChart4_gross,
				backgroundColor: warnaChart4_gross
			}, {
				label: 'Jumlah Karyawan',
				data: dataChart4_karyawan,
				backgroundColor: warnaChart4_karyawan
			}, {
				label: 'Penghasilan Rata-rata (dalam ratusan ribu)',
				data: dataChart4_rata2,
				backgroundColor: warnaChart4_rata2
			}]
		},
		options: {
			cornerRadius: 10,
			responsive: true,
			legend: {
				position: 'bottom',
			},
			scales: option_scales
		}
	});

	// ==============================================================
	// Akumulasi Tahunan (Pajak dan BPJS)
	// ==============================================================

	var canvas5 = document.getElementById('chart5').getContext('2d');
	window.chart5 = new Chart(canvas5, {
		type: 'doughnut',
		data: {
			labels: ['Pajak yang Dibayar (Dalam Jutaan)', 'BPJS Kesehatan (Dalam Jutaan)', 'BPJS Ketenagakerjaan (Dalam Jutaan)'],
			datasets: [{
				data: dataChart5,
				backgroundColor: warnaChart5
			}]
		},
		options: {
			responsive: true,
			legend: {
				position: 'bottom',
			}
		}
	});


	// ==============================================================
	// Jumlah BPJS Kesehatan dan Ketenagakerjaan
	// ==============================================================

	var canvas6 = document.getElementById('chart6').getContext('2d');
	window.chart6 = new Chart(canvas6, {
		type: 'bar',
		data: {
			labels: this.MONTHS,
			datasets: [{
				label: 'BPJS Kesehatan (Dalam Jutaan)',
				data: dataChart6_kes,
				backgroundColor: warnaChart6_kes
			}, {
				label: 'BPJS Ketenagakerjaan (Dalam Jutaan)',
				data: dataChart6_ten,
				backgroundColor: warnaChart6_ten
			}]
		},
		options: {
			cornerRadius: 10,
			responsive: true,
			legend: {
				position: 'bottom',
			},
			scales: option_scales
		}
	});
};