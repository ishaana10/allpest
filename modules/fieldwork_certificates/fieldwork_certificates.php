<?php
/**
 * modules/fieldwork_certificates/fieldwork_certificates.php
 * Certificate preview and template layout matching screenshots 'fumigation cer.png' and 'image.png'
 */
declare(strict_types=1);

$jobId = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 1);
?>
<div class="fw-container p-6 bg-slate-50 min-h-screen text-slate-800 font-sans" id="fwCertApp">

    <!-- Header breadcrumb -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="javascript:void(0)" onclick="NuApp.loadModule('fieldwork_job_orders')" class="hover:underline">Workspace</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="javascript:void(0)" onclick="NuApp.loadModule('fieldwork_certificates')" class="hover:underline">Certificates</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span class="text-slate-800 font-semibold" id="fwCertBreadcrumb">JO-2026-0142 / Preview</span>
        </div>
        <div class="flex items-center gap-3">
            <span class="px-2.5 py-1 bg-slate-200 text-slate-700 text-xs font-semibold rounded-full">● Illustrative data</span>
            <div class="flex items-center gap-2 text-xs">
                <div class="w-7 h-7 bg-teal-100 text-teal-800 font-bold rounded-full flex items-center justify-center">MN</div>
                <div>
                    <div class="font-bold text-slate-800 leading-tight">Mere Naidu</div>
                    <div class="text-[10px] text-slate-500">Authorized fumigator</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Title Bar -->
    <div class="flex items-start justify-between mb-4">
        <div>
            <div class="text-[11px] font-bold text-teal-700 uppercase tracking-wider mb-1">04 / PREPARE THE DOCUMENT</div>
            <h1 class="text-3xl font-bold text-slate-900">Certificate preview</h1>
            <p class="text-xs text-slate-500 mt-1" id="fwCertLinkedSub">Linked to JO-2026-0142 - Pacific Timber Exports Pte Ltd</p>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="window.print()" class="px-3.5 py-2 border border-slate-300 bg-white hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-md shadow-sm flex items-center gap-1.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download preview
            </button>
            <button onclick="window.print()" class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white font-semibold text-xs rounded-md shadow-sm flex items-center gap-1.5">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Print preview
            </button>
        </div>
    </div>

    <!-- Alert Banner -->
    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900 mb-6 flex items-start gap-2.5">
        <svg width="16" height="16" class="text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <div>
            <span class="font-bold">Proposed template — pending regulatory approval</span>
            <p class="text-amber-800 mt-0.5">No certificate template was supplied. This proposed layout is for review only, is not an official government certificate and must not be used as an issued certificate.</p>
        </div>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Document Preview (2 cols wide) -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg border border-slate-300 p-8 shadow-md relative overflow-hidden" id="fwCertificatePaper">

                <!-- Watermark -->
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none select-none opacity-5 rotate-[-25deg]">
                    <span class="text-7xl font-extrabold uppercase tracking-widest text-slate-900">PREVIEW / NOT ISSUED</span>
                </div>

                <div class="flex items-start justify-between border-b-2 border-teal-700 pb-4 mb-6">
                    <div>
                        <div class="font-bold text-sm text-slate-800 uppercase tracking-wide">ISLAND TREATMENT SERVICES</div>
                        <div class="text-[11px] text-slate-500">Treatment provider ID • FJ0025MB</div>
                    </div>
                    <div class="text-right">
                        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">PROPOSED TEMPLATE</div>
                        <div class="text-[10px] text-slate-400">ILLUSTRATIVE DATA</div>
                    </div>
                </div>

                <!-- Certificate Title -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Fumigation certificate</h2>
                    </div>
                    <div class="text-right">
                        <span class="px-2.5 py-1 bg-slate-100 text-slate-600 text-[11px] font-bold rounded border border-slate-200 inline-block mb-1">● Preview / Not issued</span>
                        <div class="text-[10px] text-slate-400">No issue date assigned</div>
                    </div>
                </div>

                <!-- Warning Callout inside Document -->
                <div class="p-3 bg-amber-50/70 border border-amber-200 rounded text-xs text-amber-900 mb-6">
                    <span class="font-bold">Proposed template pending regulatory approval</span>
                    <p class="text-[11px] text-amber-800 mt-0.5">For layout review only. Not an official government certificate.</p>
                </div>

                <!-- Reference Header -->
                <div class="grid grid-cols-2 gap-4 pb-4 mb-6 border-b border-slate-100 text-xs">
                    <div>
                        <div class="text-slate-400 uppercase font-semibold text-[10px]">CERTIFICATE REFERENCE</div>
                        <div class="font-bold text-slate-800 font-mono mt-0.5" id="fwCertRefCode">PREVIEW-JO-2026-0142</div>
                    </div>
                    <div>
                        <div class="text-slate-400 uppercase font-semibold text-[10px]">JOB / RECORD REFERENCE</div>
                        <div class="font-bold text-slate-800 font-mono mt-0.5" id="fwCertJobRecordRef">JO-2026-0142 / RF-2026-0142</div>
                    </div>
                </div>

                <!-- Document Body Details -->
                <div class="space-y-4 text-xs">
                    <div>
                        <div class="text-slate-400 uppercase font-semibold text-[10px]">CLIENT</div>
                        <div class="font-bold text-slate-800 mt-0.5" id="fwCertClientName">Pacific Timber Exports Pte Ltd</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <div class="text-slate-400 uppercase font-semibold text-[10px]">DESCRIPTION OF CONSIGNMENT</div>
                            <div class="font-semibold text-slate-800 mt-0.5" id="fwCertConsignment">Sawn timber • 1 × 20 ft container</div>
                        </div>
                        <div>
                            <div class="text-slate-400 uppercase font-semibold text-[10px]">CONSIGNMENT / CONTAINER NUMBER</div>
                            <div class="font-bold text-slate-800 font-mono mt-0.5" id="fwCertContainer">FCIU 482671-3</div>
                        </div>
                    </div>

                    <div>
                        <div class="text-slate-400 uppercase font-semibold text-[10px]">TREATMENT LOCATION</div>
                        <div class="text-slate-800 mt-0.5" id="fwCertLocation">18 Industrial Road, Lautoka, Fiji • Postcode: —</div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <div class="text-slate-400 uppercase font-semibold text-[10px]">TREATMENT TYPE</div>
                            <div class="text-slate-800 mt-0.5" id="fwCertType">Methyl bromide fumigation<br><span class="text-slate-500">Non-perishable commodity</span></div>
                        </div>
                        <div>
                            <div class="text-slate-400 uppercase font-semibold text-[10px]">TREATMENT DATE</div>
                            <div class="text-slate-800 mt-0.5" id="fwCertDate">19 October 2026</div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-2">
                        <div>
                            <div class="text-slate-400 uppercase font-semibold text-[10px]">TREATMENT PROVIDER</div>
                            <div class="text-slate-800 mt-0.5">Allpest and hygiene solutions Pte ltd<br><span class="text-slate-500">Provider ID: FJ0025MB</span></div>
                        </div>
                        <div>
                            <div class="text-slate-400 uppercase font-semibold text-[10px]">FUMIGATOR IN CHARGE</div>
                            <div class="text-slate-800 mt-0.5">Mere Naidu<br><span class="text-slate-500">Accreditation number, if applicable: —</span></div>
                        </div>
                    </div>

                    <!-- Signatures & Dates -->
                    <div class="grid grid-cols-2 gap-4 pt-6 border-t border-slate-200 mt-6">
                        <div>
                            <div class="text-slate-400 uppercase font-semibold text-[10px]">FUMIGATOR SIGNATURE</div>
                            <div class="h-12 border-b border-slate-300 flex items-end pb-1 text-slate-400 italic text-xs">Mere Naidu • Reserved for issuance</div>
                        </div>
                        <div>
                            <div class="text-slate-400 uppercase font-semibold text-[10px]">ISSUE DATE</div>
                            <div class="h-12 border-b border-slate-300 flex items-end pb-1 font-bold text-slate-800 text-xs">— / Not issued</div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 pt-4 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-400">
                    <div>Illustrative data • Preview only • Not issued</div>
                    <div>Page 1 of 1</div>
                </div>
            </div>
        </div>

        <!-- Right Side Panel -->
        <div class="space-y-6">

            <!-- Source Job Summary Box -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-3">Source job</h3>
                <span class="px-2.5 py-0.5 bg-teal-100 text-teal-800 font-bold text-xs rounded-full inline-flex items-center gap-1 mb-4">
                    ● Successful
                </span>

                <div class="space-y-3 text-xs">
                    <div>
                        <div class="text-slate-400">Job reference</div>
                        <div class="font-bold text-slate-800 font-mono text-sm" id="fwCertSideJobRef">JO-2026-0142</div>
                    </div>
                    <div>
                        <div class="text-slate-400">Linked record</div>
                        <div class="font-bold text-slate-800 font-mono text-sm" id="fwCertSideRecRef">RF-2026-0142</div>
                    </div>
                    <div>
                        <div class="text-slate-400">Authorized decision</div>
                        <div class="font-semibold text-slate-800" id="fwCertSideDecision">Mere Naidu • 19 Oct 2026, 16:45 FJT</div>
                    </div>
                </div>

                <button onclick="NuApp.loadModule('fieldwork_job_orders', 'review', 'inline', 'inline', {job_id: <?= $jobId ?>})" class="w-full mt-6 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 font-semibold text-xs rounded-md flex items-center justify-center gap-1.5">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Back to job review
                </button>
            </div>

            <!-- Before this can be issued box -->
            <div class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
                <h3 class="text-sm font-bold text-slate-900 mb-2">Before this can be issued</h3>
                <p class="text-xs text-slate-500 mb-3 leading-relaxed">
                    The layout and issuance process require regulatory review. No official logos, approvals or accreditation claims have been added.
                </p>
                <p class="text-xs text-slate-500 leading-relaxed">
                    Document details are carried from the linked job and record. This preview does not certify compliance or supply treatment measurements.
                </p>
            </div>

        </div>

    </div>
</div>

<script>
(function() {
    fetch(`api/fieldwork.php?action=get_job&id=<?= $jobId ?>`)
        .then(res => res.json())
        .then(data => {
            if (data.success && data.job) {
                const j = data.job;
                const r = data.record;
                document.getElementById('fwCertBreadcrumb').innerText = `${j.job_ref} / Preview`;
                document.getElementById('fwCertLinkedSub').innerText = `Linked to ${j.job_ref} - ${j.client_name}`;
                document.getElementById('fwCertRefCode').innerText = `PREVIEW-${j.job_ref}`;
                document.getElementById('fwCertJobRecordRef').innerText = `${j.job_ref} / ${r ? r.record_ref : ''}`;
                document.getElementById('fwCertClientName').innerText = j.client_name;
                document.getElementById('fwCertConsignment').innerText = j.consignment_desc || '—';
                document.getElementById('fwCertContainer').innerText = j.container_number || '—';
                document.getElementById('fwCertLocation').innerText = `${j.street_address || ''}, ${j.suburb_city || ''}, ${j.country || ''}`;
                document.getElementById('fwCertSideJobRef').innerText = j.job_ref;
                if (r) {
                    document.getElementById('fwCertSideRecRef').innerText = r.record_ref;
                }
            }
        });
})();
</script>
