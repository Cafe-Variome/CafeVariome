<?= $this->extend('layout/master') ?>
<?= $this->section('content') ?>

<div id="cvMainCarousel" class="carousel slide" data-ride="carousel">
	<ol class="carousel-indicators">
		<li class="active" data-target="#cvMainCarousel" data-slide-to="0">&nbsp;</li>
	</ol>
	<div class="carousel-inner">
		<div class="carousel-item active">
			<div class="d-block w-100 cvslide" style="height:400px;">
				<svg viewBox="0 0 1400 900">
					<defs>
						<style type="text/css"><![CDATA[
							.stop-heading-1 { stop-color: #b9333a; }
							.stop-heading-2 { stop-color: #a41d24; }
							.stop-heading-3 { stop-color: #b9333a; }

							.stop-xray-1 { stop-color: #8a8585; }
							.stop-xray-2 { stop-color: #626161; }
							.stop-xray-3 { stop-color: #8a8585; }
							.ontology-main {
								fill: rgba(246, 182, 84, 0.8);
								stroke: rgba(182, 112, 9, 0.8);
							}
							.ontology-side {
								fill: rgba(246, 196, 118, 0.8);
								stroke: rgba(218, 140, 25, 0.8);.
							}
							.ontology-edge{
								stroke: rgba(196, 119, 5, 0.8);
								stroke-width: 2;
							}
							.dna-helix{
								transform: rotate(20deg);
							}
							]]>
						</style>
						<linearGradient id="cv-heading-gradient">
							<stop class="stop-heading-1" offset="0%"/>
							<stop class="stop-heading-2" offset="50%"/>
							<stop class="stop-heading-3" offset="100%"/>
						</linearGradient>

						<linearGradient id="cv-xray-gradient">
							<stop class="stop-xray-1" offset="0%"/>
							<stop class="stop-xray-2" offset="50%"/>
							<stop class="stop-xray-3" offset="100%"/>
						</linearGradient>
						<g id="heading-ontology">
							<circle cx="200" cy="20" r="16" stroke-width="2" class="ontology-main" />
							<line x1="184" y1="20" x2="160" y2="20" class="ontology-edge" />
							<circle cx="151" cy="20" r="9" stroke-width="2" class="ontology-side" />

							<line x1="216" y1="20" x2="240" y2="20" class="ontology-edge" />
							<circle cx="249" cy="20" r="9" stroke="red" stroke-width="2" class="ontology-side" />

							<line x1="200" y1="4" x2="200" y2="-20" class="ontology-edge" />
							<circle cx="200" cy="69" r="9" stroke="red" stroke-width="2" class="ontology-side" />

							<line x1="200" y1="36" x2="200" y2="60" class="ontology-edge" />
							<circle cx="200" cy="-29" r="9" stroke="red" stroke-width="2" class="ontology-side" />

							<line x1="188.686291501" y1="8.686291501" x2="171.71572876" y2="-8.28427124" class="ontology-edge" />
							<circle cx="165.351767722" cy="54.648232278" r="9" stroke="red" stroke-width="2" class="ontology-side" />

							<line x1="188.686291501" y1="31.313708499" x2="171.71572876" y2="48.28427124" class="ontology-edge" />
							<circle cx="234.648232278" cy="54.648232278" r="9" stroke="red" stroke-width="2" class="ontology-side" />

							<line x1="211.313708499" y1="31.313708499" x2="228.28427124" y2="48.28427124" class="ontology-edge" />
							<circle cx="234.648232278" cy="-14.648232278" r="9" stroke="red" stroke-width="2" class="ontology-side" />

							<line x1="211.313708499" y1="8.686291501" x2="228.28427124" y2="-8.28427124" class="ontology-edge" id="line8"/>
							<circle cx="165.351767722" cy="-14.648232278" r="9" stroke="red" stroke-width="2" class="ontology-side" />

							<animateTransform
									id="graph-anim"
									attributeName="transform"
									attributeType="XML"
									type="rotate"
									from="0 200 20"
									to="45 200 20"
									dur="5s"
									begin="mouseover"
									fill="freeze"
									restart="whenNotActive"
							/>
						</g>

						<g id="heading-ontology-description" opacity="0">
							<rect x="0" y="0" rx="2" ry="2" width="200" height="100" fill="white"></rect>
							<polygon points="0,45 -5,50 0,55" fill="white"></polygon>
							<text x="5" y="15" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								Cafe Variome connects patients
							</text>
							<text x="5" y="30" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								to ontologies like HPO and ORDO
							</text>
							<text x="5" y="45" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								and allows similarity-based data
							</text>
							<text x="5" y="60" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								discovery.
							</text>
						</g>
						<g id="dna-helix">
							<g fill="none" stroke="#FE5F55" stroke-miterlimit="10" stroke-width="10" >
								<!-- BEGIN HELIX CURVES -->
								<path d="M0,0
									c 31.5,0 31.5,30 63,30
									s 31.5,-30 63,-30
									  31.5,30  63,30" class="dna-helix"/>

								<path d="M188.5,0
									c -31.5,0 -31.5,30 -63,30
									s -31.5,-30  -63,-30
									  -31.5,30 -63,30" class="dna-helix"/>
								<!-- END HELIX CURVES -->
							</g>
						</g>

						<g id="dna-helix-description" opacity="0">
							<rect x="0" y="0" rx="2" ry="2" width="200" height="60" fill="white"></rect>
							<polygon points="0,25 -5,30 0,35" fill="white"></polygon>

							<text x="5" y="15" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								Cafe Variome can process health
							</text>
							<text x="5" y="30" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								data in spreadsheet format, VCF,
							</text>
							<text x="5" y="45" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								and Phenopacket files.
							</text>
							<text x="5" y="60" font-family="Arial" font-size="12" fill="black" font-weight="bold">
							</text>
						</g>

						<g id="skeleton">
							<rect x="-5" y="-5" width="55" height="65" fill="url(#cv-xray-gradient)"></rect>
							<path fill="#fff" d="M3.337,30.847c0.069,0.106,1.46,2.436,4.221,3.897c1.766,0.934,2.552,1.216,3.954,1.216
							   c1.868,0,2.446-1.297,3.139-3.114c0.271-0.711,0.885-1.358,1.604-1.744c0.128,1.193,0.794,3.153,2.314,4.598
							   c0.337,0.32,0.604,0.629,0.826,0.928h-3.918c-0.721,0-1.306,0.584-1.306,1.304v2.068c0,0.719,0.585,1.306,1.306,1.306h4.945
							   c0.201,0,0.39-0.051,0.559-0.132c0.695,1.593,2.046,1.938,4.037,2.05c2.042,0.116,4.131,1.056,6.714,1.688
							   c2.582,0.628,4.547,0.269,5.621-0.637c1.073-0.904,0.601-2.234,0.034-3.16c-0.566-0.927-0.382-1.404-0.205-2.095
							   c0,0,0.538-1.258-0.452-1.527c0,0-1.014-0.184-1.312,1.079c0,0-0.006-1.083-0.875-1.168c-0.626-0.063-1.018,0.625-1.018,0.983
							   c0,0-0.085-0.946-0.849-0.946c-0.767,0-0.915,1.077-0.915,1.077s0.049-1.025-0.934-1.025c0,0-0.82-0.021-0.93,1.059
							   c0,0,0.099-1.079-0.946-1.145c0,0-0.851,0.123-0.851,1.111c0,0,0.034-1.111-0.881-1.111c-0.989,0-0.894,1.255-0.894,1.255
							   s-1.338,0.005-2.113-2.268c-0.532-1.562-1.125-4.288-1.439-5.823c0.292-0.005,0.604,0.005,1.02,0.005c0,0,1.305-0.068,2.087,1.734
							   c0.424,0.98,0.424,1.438,0.751,3.071c0,0,0.104,1.24,1.015,1.238c0.773-0.002,0.863-0.759,0.869-1.055
							   c0.008,0.316,0.102,1.177,0.954,1.12c0,0,0.788-0.028,0.854-1.01c0,0,0.13,1.078,0.98,1.078c0,0,0.914,0.099,0.93-0.996
							   c0.051,1.029,1.033,1.026,1.033,1.026c0.944-0.05,0.999-0.978,0.999-0.978c0,0.394,0.305,1.013,0.926,1.013
							   c0.834,0,0.946-0.98,0.946-0.98s0.132,1.014,0.979,0.96c0.813-0.054,0.915-0.95,0.915-0.95c0.053-0.844-0.146-0.951-0.241-2.174
							   c-0.128-1.667,0.134-2.302,0.258-3.054c0.121-0.75,0.03-1.393-0.688-0.764c-0.311,0.271-1.195,0.321-1.35-0.629
							   c-0.152-0.952,1.181-2.313,1.564-3.394c0.386-1.08,0.652-0.765-0.604-2.513c-1.257-1.75-0.629-4.332-0.13-6.047
							   c0,0,0.99-1.678,0.16-4.309c-0.832-2.631-2.641-12.55-15.812-12.938c0,0-13.594-1.064-19.174,10.071
							   C-1.665,17.449,0.248,26.096,3.337,30.847z M30.097,20.064c0.514-2.803,1.718-3.663,3.153-3.297c0,0,1.317,0.659,0.992,3.406
							   c-0.246,2.059-0.775,4.72-2.313,4.671C30.071,24.786,29.665,22.417,30.097,20.064z M22.76,26.397
							   c-1.903,0.33-2.728-0.002-2.728-0.002s4.24,0.016,4.509-2.744c0.288-2.963,0.292-4.375,1.859-5.942
							   c1.569-1.569,0.885-4.252-0.291-6.212c0,0,2.355,2.364,2.113,4.309c-0.332,2.647-2.113,2.748-2.31,5.297
							   C25.718,23.651,25.471,25.925,22.76,26.397z"/>
							<path fill="#fff" d="M15.477,42.472h4.945c0.719,0,1.303,0.585,1.303,1.305v2.067c0,0.72-0.584,1.304-1.303,1.304h-4.945
								c-0.721,0-1.306-0.584-1.306-1.304v-2.067C14.171,43.057,14.756,42.472,15.477,42.472z"/>
							<path fill="#fff" d="M15.477,48.316h4.945c0.719,0,1.303,0.584,1.303,1.304v2.067c0,0.722-0.584,1.305-1.303,1.305h-4.945
               					c-0.721,0-1.306-0.583-1.306-1.305V49.62C14.171,48.9,14.756,48.316,15.477,48.316z"/>
						</g>
						<g id="medical-image-description" opacity="0">
							<rect x="0" y="0" rx="2" ry="2" width="200" height="80" fill="white"></rect>
							<polygon points="0,35 -5,40 0,45" fill="white"></polygon>
							<text x="5" y="15" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								Cafe Variome processes medical
							</text>
							<text x="5" y="30" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								images that come with patient
							</text>
							<text x="5" y="45" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								data and provides discovery by
							</text>
							<text x="5" y="60" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								image.
							</text>
						</g>

						<g fill="white" stroke="#858796" id="search-icon">
							<path d="M61.455844123 61.455844123 l30 30" stroke-width="7.5"></path>
							<circle cx="36" cy="36" r="36" stroke-width="7.5"> </circle>
							<path d="M17.5,25
									l7,0
									l1.5,-1.5 l2.5,1.5
									l7,0
									l6,0
									l1.5,-1.5 l2.5,1.5
									l6,0" stroke="#8a272d"></path>
							<path d="M17.5,30
									l34,0" stroke="#8a272d"></path>
							<path d="M17.5,35
									l7,0
									l1.5,-1.5 l2.5,1.5
									l7,0
									l6,0
									l1.5,-1.5 l2.5,1.5
									l6,0" stroke="#8a272d"></path>
							<path d="M17.5,40
									l34,0" stroke="#8a272d"></path>
							<path d="M17.5,45
									l7,0
									l1.5,-1.5 l2.5,1.5
									l7,0
									l6,0
									l1.5,-1.5 l2.5,1.5
									l6,0" stroke="#8a272d"></path>
						</g>
						<g id="discovery-description" opacity="0">
							<rect x="0" y="0" rx="2" ry="2" width="220" height="80" fill="white"></rect>
							<polygon points="105,0 110,-5 115,0" fill="white"></polygon>
							<text x="5" y="15" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								Cafe Variome provides a visual and
							</text>
							<text x="5" y="30" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								user-friendly query interface for data
							</text>
							<text x="5" y="45" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								discovery. Results vary from counts
							</text>
							<text x="5" y="60" font-family="Arial" font-size="12" fill="black" font-weight="bold">
								 and subject IDs to detailed records.
							</text>
							<text x="5" y="75" font-family="Arial" font-size="12" fill="black" font-weight="bold">

							</text>
						</g>
					</defs>

					<text x="20" y="80" font-family="Arial" font-size="69" fill="#858796" font-weight="bold">Health Data</text>
					<text x="70" y="140" font-family="Arial" font-size="69" fill="url(#cv-heading-gradient)" font-weight="bold">Discovery</text>
					<text x="25" y="200" font-family="Arial" font-size="18" fill="#858796" font-weight="bold">
						Café Variome is a flexible web-based, data discovery
					</text>
					<text x="25" y="225" font-family="Arial" font-size="18" fill="#858796" font-weight="bold">
						tool that can be quickly installed by any biomedical
					</text>
					<text x="25" y="250" font-family="Arial" font-size="18" fill="#858796" font-weight="bold">
						data owner to enable the “existence” rather than
					</text>
					<text x="25" y="275" font-family="Arial" font-size="18" fill="#858796" font-weight="bold">
						the “substance” of the data to be discovered.
					</text>
					<use href="#cvmug" x="780" y="110"/>
					<use href="#heading-ontology" id="ontology-graph" x="880" y="50"/>
					<use href="#heading-ontology-description" id="ontology-description" x="1190" y="10"/>
					<path d="M 1000,30 l -160,0 l 0,30 " stroke="orange" stroke-width="1.5" fill="none" marker-start="url(#startarrow)" marker-end="url(#endarrow)"></path>

					<g id="medical-images">
						<use href="#skeleton" x="1080" y="150"/>
						<use href="#skeleton" x="1060" y="150"/>
						<use href="#skeleton" x="1040" y="150"/>
					</g>
					<path d="M 1000,175 l -80,0" stroke="orange" stroke-width="1.5" fill="none" marker-end="url(#endarrow)"></path>
					<use href="#medical-image-description" x="1190" y="150"/>

					<use href="#dna-helix" x="1000" y="250" id="genomic-data"/>
					<path d="M 1000,300 l -160,0 l 0,-30 " stroke="orange" stroke-width="1.5" fill="none" marker-end="url(#endarrow)"></path>
					<use href="#dna-helix-description" x="1190" y="250" id="genomic-data-description"/>

					<path d="M 770,160 l -80,0" stroke="orange" stroke-width="1.5" fill="none" marker-end="url(#endarrow)"></path>
					<use href="#search-icon" x="570" y="120" id="discovery-icon"/>
					<use href="#discovery-description" x="510" y="230"/>
				</svg>
			</div>
		</div>
	</div>
<!--	<a class="carousel-control-prev" role="button" href="#cvMainCarousel" data-slide="prev"> <span class="sr-only">Previous</span> </a> <a class="carousel-control-next" role="button" href="#cvMainCarousel" data-slide="next"> <span class="sr-only">Next</span> </a>-->
</div>

<div class="row mt-4">
	<div class="col">
		<div class="card cvcard">
			<div class="card-body">
				<h5 class="card-title">Federated Networks</h5>
				<hr>
				<svg viewBox="0 0 800 620" >
					<defs>
						<linearGradient id="cv-gradient">
							<stop class="stop1" offset="0%"/>
							<stop class="stop2" offset="50%"/>
							<stop class="stop3" offset="100%"/>
						</linearGradient>

						<style type="text/css"><![CDATA[
							.stop1 { stop-color: #8a272d; }
							.stop2 { stop-color: #c1262c; }
							.stop3 { stop-color: #8a272d; }
							]]>
						</style>
						<g id="cvmug">
							<g>
								<rect x="10" y="7" rx="10" ry="5" width="100" height="100" fill="url(#cv-gradient)">
							</g>
							<g>
								<rect x="40" y="25" rx="10" ry="10" width="60" height="70" fill="#ebebeb">
							</g>
							<g>
								<text x="50" y="85" font-family="Arial" font-size="63" fill="gray" font-weight="bold">V</text>
							</g>
							<g>
								<path d="M 40,45 C 20,45  15,80  40,80" fill="none" stroke="gray" stroke-width="10" />
							</g>
							<g>
								<path d="M 65,25 C 45,20  45,20  65,15" stroke="gray" fill="none" stroke-width="4"/>
								<path d="M 50,20 C 68,15  80,10  48,5" stroke="gray" fill="none" stroke-width="4"/>
								<path d="M 68,10 C 42,5  45,5  65,0" stroke="gray" fill="none" stroke-width="4"/>
							</g>
							<g>
								<path d="M 85,25 C 65,20  65,20  85,15" stroke="gray" fill="none" stroke-width="4"/>
								<path d="M 70,20 C 88,15  100,10  68,5" stroke="gray" fill="none" stroke-width="4"/>
								<path d="M 88,10 C 62,5  65,5  85,0" stroke="gray" fill="none" stroke-width="4"/>
							</g>
						</g>
						<marker id="startarrow" markerWidth="5" markerHeight="3.5" refX="5" refY="1.6" orient="auto">
							<polygon points="5 -0.25, 5 3.5, 0 1.5" fill="#c1262c" />
						</marker>
						<marker id="endarrow" markerWidth="5" markerHeight="3.5" refX="0" refY="1.6" orient="auto" markerUnits="strokeWidth">
							<polygon points="0 -0.25, 5 1.5, 0 3.5" fill="#c1262c" />
						</marker>
					</defs>
					<use xlink:href="#cvmug" x="330" y="5"/>
					<line x1="310" y1="135" x2="240" y2="200" stroke="gray" stroke-width="6" marker-end="url(#endarrow)" marker-start="url(#startarrow)" />
					<use xlink:href="#cvmug" x="100" y="220"/>
					<line x1="465" y1="135" x2="535" y2="200" stroke="gray" stroke-width="6" marker-end="url(#endarrow)" marker-start="url(#startarrow)" />
					<use xlink:href="#cvmug" x="550" y="220"/>
					<line x1="535" y1="355" x2="465" y2="420" stroke="gray" stroke-width="6" marker-end="url(#endarrow)" marker-start="url(#startarrow)" />
					<use xlink:href="#cvmug" x="330" y="440"/>
					<line x1="240" y1="350" x2="310" y2="420" stroke="gray" stroke-width="6" marker-end="url(#endarrow)" marker-start="url(#startarrow)" />

					<line x1="387.5" y1="150" x2="387.5" y2="405" stroke="gray" stroke-dasharray="2" stroke-width="6" marker-end="url(#endarrow)" marker-start="url(#startarrow)" />
					<line x1="245" y1="277.5" x2="525" y2="277.5" stroke="gray" stroke-dasharray="2" stroke-width="6" marker-end="url(#endarrow)" marker-start="url(#startarrow)" />

				</svg>
				<hr>
				Networks allow data discovery to go beyond an individual Café Variome instance.<br>
				Administrators can create new networks as well as request to join existing ones.
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card cvcard">
			<div class="card-body">
				<h5 class="card-title">User-friendly Interface</h5>
				<hr>
				<svg viewBox="0 0 940 730" >
					<defs>
						<linearGradient id="cv-admin-bg-gradient" gradientTransform="rotate(90)">
							<stop class="stop-main-1" offset="0%"/>
							<stop class="stop-main-2" offset="25%"/>
							<stop class="stop-main-3" offset="75%"/>
							<stop class="stop-main-4" offset="100%"/>
						</linearGradient>
						<linearGradient id="cv-admin-bg-gradient-side" gradientTransform="rotate(90)">
							<stop class="stop-side-1" offset="0%"/>
							<stop class="stop-side-2" offset="25%"/>
							<stop class="stop-side-3" offset="75%"/>
							<stop class="stop-side-4" offset="100%"/>
						</linearGradient>
						<style type="text/css"><![CDATA[
							.stop-main-1 { stop-color: #EBECEFFF; }
							.stop-main-2 { stop-color: #f8f9fc; }
							.stop-main-3 { stop-color: #f8f9fc; }
							.stop-main-4 { stop-color: #EBECEFFF; }

							.stop-side-1 { stop-color: #9b9ba1; }
							.stop-side-2 { stop-color: #858796; }
							.stop-side-3 { stop-color: #858796; }
							.stop-side-4 { stop-color: #9b9ba1; }
							]]>
						</style>

						<g id="bg-main">
							<rect width="730" height="500" rx="2" ry="2" style="fill:url(#cv-admin-bg-gradient);stroke-width:3;stroke:gray" />
						</g>
						<g id="bg-side">
							<rect width="200" height="500" rx="2" ry="2" style="fill:url(#cv-admin-bg-gradient-side);stroke-width:3;stroke:#858796" />
						</g>

						<g id="card-shape">
							<rect width="150" height="75" rx="2" ry="2" fill="#fff" stroke="none" />
						</g>

						<g id="bar-chart-header">
							<rect width="400" height="30" rx="2" ry="2" fill="#eaecf4" stroke="#e3e6f0" />
						</g>

						<g id="bar-chart-bg">
							<rect width="400" height="250" rx="2" ry="2" fill="#fff" stroke="none" />
						</g>

						<g id="bar-chart-1">
							<rect width="30" height="150" rx="2" ry="2" fill="#36a2eb" stroke="none" />
						</g>
						<g id="bar-chart-2">
							<rect width="30" height="80" rx="2" ry="2" fill="#36a2eb" stroke="none" />
						</g>
						<g id="bar-chart-3">
							<rect width="30" height="100" rx="2" ry="2" fill="#36a2eb" stroke="none" />
						</g>
						<g id="bar-chart-4">
							<rect width="30" height="150" rx="2" ry="2" fill="#36a2eb" stroke="none" />
						</g>
						<g id="bar-chart-5">
							<rect width="30" height="80" rx="2" ry="2" fill="#36a2eb" stroke="none" />
						</g>
						<g id="bar-chart-6">
							<rect width="30" height="120" rx="2" ry="2" fill="#36a2eb" stroke="none" />
						</g>
						<g id="bar-chart-7">
							<rect width="30" height="80" rx="2" ry="2" fill="#36a2eb" stroke="none" />
						</g>
						<g id="bar-chart-8">
							<rect width="30" height="100" rx="2" ry="2" fill="#36a2eb" stroke="none" />
						</g>

						<g id="menu-circle">
							<circle cx="4" cy="5" r="4" stroke="#858796" stroke-width="2" fill="#EBECEFFF" />
						</g>
						<g id="menu-text">
							<rect width="150" height="15" rx="2" ry="2" fill="#f8f9fc" stroke="none" />
						</g>

						<g id="donut-chart-header">
							<rect width="200" height="30" rx="2" ry="2" fill="#eaecf4" stroke="#e3e6f0" />
						</g>

						<g id="donut-chart-bg">
							<rect width="200" height="160" rx="2" ry="2" fill="#fff" stroke="none" />
						</g>
						<g id="donut-chart" stroke-width='30'>
							<circle cx='0' cy='0' r='58' stroke-dasharray='-1' stroke-dashoffset='-15' stroke='rgb(144, 238, 144)' fill="none"></circle>
							<circle cx='0' cy='0' r='58' stroke-dasharray='190' stroke-dashoffset='20' stroke='rgb(255, 165, 0)' fill="none"></circle>
						</g>

					</defs>

					<use xlink:href="#bg-main" x="200" y="0"/>
					<use xlink:href="#bg-side" x="0" y="0"/>

					<line x1="250" y1="50" x2="250" y2="125" stroke="#4e73df" stroke-width="4"/>
					<use xlink:href="#card-shape" x="250" y="50"/>
					<line x1="420" y1="50" x2="420" y2="125" stroke="#1cc88a" stroke-width="4"/>
					<use xlink:href="#card-shape" x="420" y="50"/>
					<line x1="590" y1="50" x2="590" y2="125" stroke="#36b9cc" stroke-width="4"/>
					<use xlink:href="#card-shape" x="590" y="50"/>
					<line x1="760" y1="50" x2="760" y2="125" stroke="#f6c23e" stroke-width="4"/>
					<use xlink:href="#card-shape" x="760" y="50"/>

					<use xlink:href="#bar-chart-header" x="250" y="150"/>

					<use xlink:href="#bar-chart-bg" x="250" y="180" />
					<use xlink:href="#bar-chart-1" x="290" y="250"/>
					<use xlink:href="#bar-chart-2" x="330" y="320"/>
					<use xlink:href="#bar-chart-3" x="370" y="300"/>
					<use xlink:href="#bar-chart-4" x="410" y="250"/>
					<use xlink:href="#bar-chart-5" x="450" y="320"/>
					<use xlink:href="#bar-chart-6" x="490" y="280"/>
					<use xlink:href="#bar-chart-7" x="530" y="320"/>
					<use xlink:href="#bar-chart-8" x="570" y="300"/>

					<line x1="270" y1="410" x2="630" y2="410" stroke="gray" stroke-width="1"/>
					<line x1="275" y1="415" x2="275" y2="210" stroke="gray" stroke-width="1"/>

					<use xlink:href="#menu-circle" x="18" y="52"/>
					<use xlink:href="#menu-text" x="30" y="50"/>

					<use xlink:href="#menu-circle" x="18" y="72"/>
					<use xlink:href="#menu-text" x="30" y="70"/>

					<use xlink:href="#menu-circle" x="18" y="92"/>
					<use xlink:href="#menu-text" x="30" y="90"/>

					<use xlink:href="#donut-chart-header" x="690" y="150"/>
					<use xlink:href="#donut-chart-bg" x="690" y="180"/>
					<use xlink:href="#donut-chart" x="790" y="260"/>

					<use xlink:href="#donut-chart-header" x="690" y="350"/>
				</svg>
				<hr>
				The administration dashboard gives you full control over your Café Variome installation.
				You can manage data sources, networks, users list and user access all in one dashboard.
			</div>
		</div>
	</div>
	<div class="col">
		<div class="card cvcard">
			<div class="card-body">
				<h5 class="card-title">Beacon Endpoints</h5>
				<hr>
				<p class="text-center">
					<img src="<?= base_url() ?>/resources/images/logos/beacon-logo.png">
				</p>
				<br><br><br>
				<hr>
				Café Variome implements a Beacon V2.0 API. Beacon is an API
				that aims to make discovering genomic data easier.
				By uploading or inserting data to Café Variome,
				you have the option to make it discoverable through Beacon.
			</div>
		</div>
	</div>
</div>
<hr>
<div class="row mt-6">
	<div class="col-2">
		<p class="text-center text-info" style="font-size:100px"><i class="fa fa-search"></i></p>
	</div>
	<div class="col-10">
		<h4>Fast, Memory-efficient, and Secure Discovery</h4>
		<p>
			Café Variome executes queries at a high speed and has very little memory foot-print enabling it to be hosted on small servers.
			Apart from discovery, large data files of millions of records can be indexed in a memory-efficient way using the data input pipeline of Café Variome.
			<br>
			Café Variome relies on OIDC providers for authentication. Queries have a bearer token used to authenticate
			and authorise data discovery.
		</p>
	</div>
</div>
<hr>
<div class="row mt-4">
	<div class="col-8">
		<h4>Discovery based on semantic similarity</h4>
		<p>
			Use of ontology terms in patient data has become more prevalent. Café Variome can work with any health data ontology and can be configured to
			automatically detect ontology terms in raw data. This enables Café Variome to connect patients to nodes in ontologies. Similarity scores among
			ontology terms are calculated. Therefore, you can discover <i>similar</i> patients based on phenotypes, diseases, or medicine that they use.

		</p>
	</div>
	<div class="col-4">
		<canvas id="similarity-graph" width="350" height="200"></canvas>
	</div>
</div>
<?= $this->endSection() ?>
