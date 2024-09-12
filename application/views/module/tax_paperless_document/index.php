<section class="section">
	<div class="row">
		<div class="col-md-4 mx-auto">
			<div class="pagetitle">
				<h1>Paperless Document</h1>
				<nav>
					<ol class="breadcrumb">
						<li class="breadcrumb-item"><a href="<?= base_url() ?>dashboard">Dashboard</a></li>
						<li class="breadcrumb-item active">Paperless Document</li>
					</ol>
				</nav>
			</div>
			<div class="card">
				<div class="card-body">
					<h5 class="card-title">Code</h5>
					<div class="row">
						<div class="col-md-12">
							<textarea class="form-control" id="txt_html" style="height: 100px"></textarea>
						</div>
						<div class="col-md-12 d-none" id="bl_html">
						</div>
						<div class="col-md-12 pt-3 text-center">
							<button type="button" class="btn btn-primary" id="btn_show">Register</button>
							<a href="<?= base_url() ?>module/tax_paperless_document/download" class="btn btn-secondary" target="_blank">Download</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
<script>
function upload_invoice(){
	var rows = [];

	$.each($("#tabla tr"), function(index, value) {
		if (index > 0){
			var doms = $(value).find("td");
			var urlObj = new URL($($(doms[11]).html())[0].href);
			var row = {
				doc_type: $(doms[1]).html(),
				doc_number: $($(doms[2]).html()).first().html().replace(/&nbsp;/g, ''),
				customer_id: $($(doms[3]).html()).first().html().replace(/&nbsp;/g, ''),
				customer_name: $(doms[4]).html().replace(/&nbsp;/g, ''),
				date_enter: $($(doms[5]).html()).first().html().replace(/&nbsp;/g, ''),
				date_issue: $($(doms[6]).html()).first().html().replace(/&nbsp;/g, ''),
				amount: $($(doms[7]).html()).first().html().replace(/&nbsp;/g, '').replace(/,/g, ''),
				currency: $($(doms[8]).html()).first().html().replace(/&nbsp;/g, ''),
				status: $($($(doms[10]).html()).first().html()).attr("title"),
				paperless_id: urlObj.searchParams.get("id"),
			};
			
			rows.push(row);
		}
	});
	
	//console.log(rows);
	ajax_simple({rows:rows}, "module/tax_paperless_document/upload").done(function(res) {
		swal(res.type, res.msg);
		$('#txt_html').val("");
		//console.log(res.msg);
	});
}

function load_from_file(filename){
	$.get('/llamasys/upload/' + filename, function(data) {
		$('#bl_html').html(data);
		
		upload_invoice();
	});
}

document.addEventListener("DOMContentLoaded", () => {
	$("#btn_show").on("click", function() {
		$("#bl_html").html($("#txt_html").val());
		
		upload_invoice();
	});
	
	/*
	let fileIndex = 1;  // 파일 이름을 생성하기 위한 초기 인덱스

	console.log("File: " + fileIndex + "_paperless.txt");
	
	load_from_file(fileIndex + "_paperless.txt");
	fileIndex++;  // 다음 파일 이름 생성을 위해 인덱스 증가
	
	setInterval(function() {
		console.log("File: " + fileIndex + "_paperless.txt");
		
		load_from_file(fileIndex + "_paperless.txt");
		fileIndex++;  // 다음 파일 이름 생성을 위해 인덱스 증가
	}, 30000);  // 30초(30,000ms)마다 실행
	*/
});
</script>