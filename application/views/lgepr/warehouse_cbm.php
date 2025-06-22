<div class="row">
	<div class="col-12">
		<div class="card mt-4">
			<div class="card-body py-0">
				<div class="d-flex justify-content-between align-items-center">
					<div class="d-flex justify-content-start align-items-center">
						<a type="button" class="btn btn-primary me-3 d-none" href="<?= base_url() ?>lgepr/reports"><i class="bi bi-arrow-left"></i></a>
						<h5 class="card-title py-3 my-0">Warehouse CBM Simulation . <?= $from ?> ~ <?= $to ?></h5>
					</div>
					<div class="d-flex justify-content-end align-items-center">
						<ul class="nav nav-pills" role="tablist">
							<li class="nav-item" role="presentation">
								<button class="nav-link active" id="by_3pl-tab" data-bs-toggle="tab" data-bs-target="#by_3pl" type="button" role="tab" aria-controls="by_3pl" aria-selected="true">By 3PL</button>
							</li>
							<li class="nav-item" role="presentation">
								<button class="nav-link" id="by_org-tab" data-bs-toggle="tab" data-bs-target="#by_org" type="button" role="tab" aria-controls="by_org" aria-selected="false" tabindex="-1">By ORG</button>
							</li>
						</ul>
					</div>
				</div>
				<div class="tab-content pt-2">
					<div class="tab-pane fade show active" id="by_3pl" role="tabpanel" aria-labelledby="by_3pl-tab">
						<?php foreach($by_3pl as $ware => $years){ ?>
						<div class="card">
							<div class="card-body">
								<h5 class="card-title"><?= $ware ?></h5>
								<table class="table table-sm table-bordered">
									<thead id="thead_container">
										<tr class="align-middle">
											<th scope="col">Year</th>
											<?php foreach($years as $year => $months){ $total_col = count($months); foreach($months as $month => $days) $total_col += count($days); ?>
											<th scope="col" colspan="<?= $total_col ?>"><?= $year ?></th>
											<?php } ?>
										</tr>
										<tr class="align-middle">
											<th scope="col">Month</th>
											<?php foreach($years as $year => $months){ foreach($months as $month => $days){ ?>
											<th scope="col" colspan="<?= count($days) ?>"><?= $month ?></th>
											<?php }} ?>
										</tr>
										<tr class="align-middle">
											<th scope="col">Day</th>
											<?php foreach($years as $year => $months){ foreach($months as $month => $days){ foreach($days as $day => $divs){ ?>
											<th scope="col"><div style="width:60px;"><?= $day ?></div></th>
											<?php }}} ?>
										</tr>
									</thead>
									<tbody class="table-group-divider">
										<tr>
											<td>
												<div>Total</div>
												<div><small class="text-danger">Arrival</small></div>
												<div><small class="text-primary">Sales</small></div>
											</td>
											<?php 
											foreach($years as $year => $months){ 
												foreach($months as $month => $days){ 
													foreach($days as $day => $divs){ ?>
											<td class="text-end">
												<div><?= $divs["Total"]["progress"] ? number_format($divs["Total"]["progress"]) : "&nbsp;" ?></div>
												<div><small class="text-danger"><?= $divs["Total"]["arrival"] ? number_format($divs["Total"]["arrival"]) : "&nbsp;" ?></small></div>
												<div><small class="text-primary"><?= $divs["Total"]["sales"] ? number_format($divs["Total"]["sales"]) : "&nbsp;" ?></small></div>
											</td>
											<?php }}} ?>
										</tr>
										<tr>
											<td>
												<div>HS</div>
												<div><small class="text-danger">Arrival</small></div>
												<div><small class="text-primary">Sales</small></div>
											</td>
											<?php 
											foreach($years as $year => $months){ 
												foreach($months as $month => $days){ 
													foreach($days as $day => $divs){ ?>
											<td class="text-end">
												<div><?= $divs["HS"]["progress"] ? number_format($divs["HS"]["progress"]) : "&nbsp;" ?></div>
												<div><small class="text-danger"><?= $divs["HS"]["arrival"] ? number_format($divs["HS"]["arrival"]) : "&nbsp;" ?></small></div>
												<div><small class="text-primary"><?= $divs["HS"]["sales"] ? number_format($divs["HS"]["sales"]) : "&nbsp;" ?></small></div>
											</td>
											<?php }}} ?>
										</tr>
										<tr>
											<td>
												<div>MS</div>
												<div><small class="text-danger">Arrival</small></div>
												<div><small class="text-primary">Sales</small></div>
											</td>
											<?php 
											foreach($years as $year => $months){ 
												foreach($months as $month => $days){ 
													foreach($days as $day => $divs){ ?>
											<td class="text-end">
												<div><?= $divs["MS"]["progress"] ? number_format($divs["MS"]["progress"]) : "&nbsp;" ?></div>
												<div><small class="text-danger"><?= $divs["MS"]["arrival"] ? number_format($divs["MS"]["arrival"]) : "&nbsp;" ?></small></div>
												<div><small class="text-primary"><?= $divs["MS"]["sales"] ? number_format($divs["MS"]["sales"]) : "&nbsp;" ?></small></div>
											</td>
											<?php }}} ?>
										</tr>
										<tr>
											<td>
												<div>ES</div>
												<div><small class="text-danger">Arrival</small></div>
												<div><small class="text-primary">Sales</small></div>
											</td>
											<?php 
											foreach($years as $year => $months){ 
												foreach($months as $month => $days){ 
													foreach($days as $day => $divs){ ?>
											<td class="text-end">
												<div><?= $divs["ES"]["progress"] ? number_format($divs["ES"]["progress"]) : "&nbsp;" ?></div>
												<div><small class="text-danger"><?= $divs["ES"]["arrival"] ? number_format($divs["ES"]["arrival"]) : "&nbsp;" ?></small></div>
												<div><small class="text-primary"><?= $divs["ES"]["sales"] ? number_format($divs["ES"]["sales"]) : "&nbsp;" ?></small></div>
											</td>
											<?php }}} ?>
										</tr>
										
									</tbody>
								</table>
							</div>
						</div>
						<?php } ?>
					</div>
					<div class="tab-pane fade" id="by_org" role="tabpanel" aria-labelledby="by_org-tab">
						<?php foreach($by_org as $org => $years){ ?>
						<div class="card">
							<div class="card-body">
								<h5 class="card-title"><?= $org ?></h5>
								<table class="table table-sm table-bordered">
									<thead id="thead_container">
										<tr class="align-middle">
											<th scope="col">Year</th>
											<?php foreach($years as $year => $months){ $total_col = count($months); foreach($months as $month => $days) $total_col += count($days); ?>
											<th scope="col" colspan="<?= $total_col ?>"><?= $year ?></th>
											<?php } ?>
										</tr>
										<tr class="align-middle">
											<th scope="col">Month</th>
											<?php foreach($years as $year => $months){ foreach($months as $month => $days){ ?>
											<th scope="col" colspan="<?= count($days) ?>"><?= $month ?></th>
											<?php }} ?>
										</tr>
										<tr class="align-middle">
											<th scope="col">Day</th>
											<?php foreach($years as $year => $months){ foreach($months as $month => $days){ foreach($days as $day => $divs){ ?>
											<th scope="col"><div style="width:60px;"><?= $day ?></div></th>
											<?php }}} ?>
										</tr>
									</thead>
									<tbody class="table-group-divider">
										<tr>
											<td>
												<div>Total</div>
												<div><small class="text-danger">Arrival</small></div>
												<div><small class="text-primary">Sales</small></div>
											</td>
											<?php 
											foreach($years as $year => $months){ 
												foreach($months as $month => $days){ 
													foreach($days as $day => $divs){ ?>
											<td class="text-end">
												<div><?= $divs["Total"]["progress"] ? number_format($divs["Total"]["progress"]) : "&nbsp;" ?></div>
												<div><small class="text-danger"><?= $divs["Total"]["arrival"] ? number_format($divs["Total"]["arrival"]) : "&nbsp;" ?></small></div>
												<div><small class="text-primary"><?= $divs["Total"]["sales"] ? number_format($divs["Total"]["sales"]) : "&nbsp;" ?></small></div>
											</td>
											<?php }}} ?>
										</tr>
										<tr>
											<td>
												<div>HS</div>
												<div><small class="text-danger">Arrival</small></div>
												<div><small class="text-primary">Sales</small></div>
											</td>
											<?php 
											foreach($years as $year => $months){ 
												foreach($months as $month => $days){ 
													foreach($days as $day => $divs){ ?>
											<td class="text-end">
												<div><?= $divs["HS"]["progress"] ? number_format($divs["HS"]["progress"]) : "&nbsp;" ?></div>
												<div><small class="text-danger"><?= $divs["HS"]["arrival"] ? number_format($divs["HS"]["arrival"]) : "&nbsp;" ?></small></div>
												<div><small class="text-primary"><?= $divs["HS"]["sales"] ? number_format($divs["HS"]["sales"]) : "&nbsp;" ?></small></div>
											</td>
											<?php }}} ?>
										</tr>
										<tr>
											<td>
												<div>MS</div>
												<div><small class="text-danger">Arrival</small></div>
												<div><small class="text-primary">Sales</small></div>
											</td>
											<?php 
											foreach($years as $year => $months){ 
												foreach($months as $month => $days){ 
													foreach($days as $day => $divs){ ?>
											<td class="text-end">
												<div><?= $divs["MS"]["progress"] ? number_format($divs["MS"]["progress"]) : "&nbsp;" ?></div>
												<div><small class="text-danger"><?= $divs["MS"]["arrival"] ? number_format($divs["MS"]["arrival"]) : "&nbsp;" ?></small></div>
												<div><small class="text-primary"><?= $divs["MS"]["sales"] ? number_format($divs["MS"]["sales"]) : "&nbsp;" ?></small></div>
											</td>
											<?php }}} ?>
										</tr>
										<tr>
											<td>
												<div>ES</div>
												<div><small class="text-danger">Arrival</small></div>
												<div><small class="text-primary">Sales</small></div>
											</td>
											<?php 
											foreach($years as $year => $months){ 
												foreach($months as $month => $days){ 
													foreach($days as $day => $divs){ ?>
											<td class="text-end">
												<div><?= $divs["ES"]["progress"] ? number_format($divs["ES"]["progress"]) : "&nbsp;" ?></div>
												<div><small class="text-danger"><?= $divs["ES"]["arrival"] ? number_format($divs["ES"]["arrival"]) : "&nbsp;" ?></small></div>
												<div><small class="text-primary"><?= $divs["ES"]["sales"] ? number_format($divs["ES"]["sales"]) : "&nbsp;" ?></small></div>
											</td>
											<?php }}} ?>
										</tr>
										
									</tbody>
								</table>
							</div>
						</div>
						<?php } ?>
					</div>
				</div>
				
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-12">
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
	
});
</script>