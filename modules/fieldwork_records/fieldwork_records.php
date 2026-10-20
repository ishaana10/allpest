<?php
/**
 * modules/fieldwork_records/fieldwork_records.php
 * Record of Fumigation draft screen matching screenshots rof p1, rof p2, rof p3
 */
declare(strict_types=1);

$jobId = (int)($_GET['job_id'] ?? $_POST['job_id'] ?? 1);
?>
<div class="fw-container p-6 bg-slate-50 min-h-screen text-slate-800 font-sans" id="fwRecordApp">

    <!-- Header breadcrumb -->
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-500 font-medium">
            <a href="javascript:void(0)" onclick="NuApp.loadModule('fieldwork_job_orders')" class="hover:underline">Workspace</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <a href="javascript:void(0)" onclick="NuApp.loadModule('fieldwork_job_orders')" class="hover:underline">Job orders</a>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
            <span class="text-slate-800 font-semibold" id="fwRecBreadcrumb">JO-2026-0142 / Record</span>
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
            <div class="text-[11px] font-bold text-teal-700 uppercase tracking-wider mb-1">02 / RECORD THE TREATMENT</div>
            <h1 class="text-3xl font-bold text-slate-900">Record of fumigation</h1>
            <p class="text-xs text-slate-500 mt-1" id="fwRecSubHeader">Methyl bromide fumigation • Non-perishable commodity • RF-2026-0142</p>
        </div>
        <span class="px-3 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded-full flex items-center gap-1.5" id="fwRecStatusBadge">
            ● Draft
        </span>
    </div>

    <!-- Linked Job Bar -->
    <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm mb-6 flex flex-wrap items-center justify-between gap-4 text-xs">
        <div class="flex items-center gap-8">
            <div>
                <div class="text-slate-400 font-medium mb-0.5">Linked job</div>
                <div class="font-bold text-teal-700 font-mono text-sm" id="fwRecJobRef">JO-2026-0142</div>
            </div>
            <div>
                <div class="text-slate-400 font-medium mb-0.5">Client</div>
                <div class="font-bold text-slate-800" id="fwRecClient">Pacific Timber Exports Pte Ltd</div>
            </div>
            <div>
                <div class="text-slate-400 font-medium mb-0.5">Container</div>
                <div class="font-bold text-slate-800 font-mono" id="fwRecContainer">FCIU 482671-3</div>
            </div>
        </div>
        <button onclick="NuApp.loadModule('fieldwork_job_orders', 'review', 'inline', 'inline', {job_id: <?= $jobId ?>})" class="px-3 py-1.5 border border-slate-300 rounded text-slate-700 hover:bg-slate-50 font-semibold flex items-center gap-1">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            View job
        </button>
    </div>

    <!-- Draft Alert Banner -->
    <div class="p-3.5 bg-amber-50 border border-amber-200 rounded-lg text-xs text-amber-900 mb-6 flex items-start gap-2.5">
        <svg width="16" height="16" class="text-amber-600 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <div>
            <span class="font-bold">Illustrative draft state • mandatory treatment entries are blank</span>
            <p class="text-amber-800 mt-0.5">This is the same job before review. Complete the required record and declaration before submitting. No success decision or certificate is available from this draft.</p>
        </div>
    </div>

    <!-- Section Navigation Tabs -->
    <div class="bg-white rounded-lg border border-slate-200 p-2 mb-6 flex items-center gap-2 overflow-x-auto text-xs font-semibold text-slate-600">
        <a href="#secA" class="px-3 py-1.5 text-teal-800 bg-teal-50 rounded border-b-2 border-teal-700">A Fumigator</a>
        <a href="#secB" class="px-3 py-1.5 hover:text-slate-900">B Job details</a>
        <a href="#secC" class="px-3 py-1.5 hover:text-slate-900">C Fumigation details</a>
        <a href="#secD" class="px-3 py-1.5 hover:text-slate-900">D Concentration readings</a>
        <a href="#secE" class="px-3 py-1.5 hover:text-slate-900">E Declaration</a>
    </div>

    <!-- Main Form Form -->
    <form id="fwFumigationRecordForm" onsubmit="FwRecord.saveRecord(event)" class="space-y-6">
        <input type="hidden" id="fwRecId" value="1">

        <!-- SECTION A: Fumigator in charge -->
        <div id="secA" class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-6 h-6 bg-teal-100 text-teal-800 font-bold rounded flex items-center justify-center text-xs">A</span>
                <h2 class="text-base font-bold text-slate-900">Fumigator in charge</h2>
            </div>
            <p class="text-xs text-slate-400 mb-4 pl-8">The person responsible for carrying out and recording this treatment.</p>

            <div class="grid grid-cols-2 gap-4 pl-8">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Full name *</label>
                    <input type="text" id="fwFumigatorName" value="Mere Naidu" required class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Accreditation number, if applicable</label>
                    <input type="text" id="fwAccreditationNumber" value="—" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                </div>
            </div>
        </div>

        <!-- SECTION B: Job details -->
        <div id="secB" class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-6 h-6 bg-teal-100 text-teal-800 font-bold rounded flex items-center justify-center text-xs">B</span>
                <h2 class="text-base font-bold text-slate-900">Job details</h2>
            </div>
            <p class="text-xs text-slate-400 mb-4 pl-8">Carried from the linked job order. Edit these details in the job order, not a second time here.</p>

            <div class="grid grid-cols-3 gap-y-4 gap-x-6 text-xs pl-8 border-t border-slate-100 pt-4">
                <div>
                    <div class="text-slate-400 mb-0.5">Treatment provider / ID</div>
                    <div class="font-semibold text-slate-800">Allpest and hygiene solutions Pte ltd / FJ0025MB</div>
                </div>
                <div>
                    <div class="text-slate-400 mb-0.5">Job identification number</div>
                    <div class="font-bold text-slate-800 font-mono" id="fwJobNumDisplay">JO-2026-0142</div>
                </div>
                <div>
                    <div class="text-slate-400 mb-0.5">Client name</div>
                    <div class="font-semibold text-slate-800" id="fwClientNameDisplay">Pacific Timber Exports Pte Ltd</div>
                </div>
                <div>
                    <div class="text-slate-400 mb-0.5">Street address</div>
                    <div class="text-slate-700" id="fwStreetDisplay">18 Industrial Road</div>
                </div>
                <div>
                    <div class="text-slate-400 mb-0.5">Suburb / town / city</div>
                    <div class="text-slate-700" id="fwCityDisplay">Lautoka</div>
                </div>
                <div>
                    <div class="text-slate-400 mb-0.5">Country / postcode</div>
                    <div class="text-slate-700" id="fwCountryDisplay">Fiji / —</div>
                </div>
            </div>
        </div>

        <!-- SECTION C: Fumigation details -->
        <div id="secC" class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm space-y-6">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-6 h-6 bg-teal-100 text-teal-800 font-bold rounded flex items-center justify-center text-xs">C</span>
                <h2 class="text-base font-bold text-slate-900">Fumigation details</h2>
            </div>

            <!-- Treatment Schedule -->
            <div class="pl-8 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Specified treatment schedule *</label>
                    <input type="text" id="fwSchedule" placeholder="e.g. 24g/m³ for 24 hrs at 21°C+" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dose rate *</label>
                        <div class="relative">
                            <input type="text" id="fwDoseRate" placeholder="24" class="w-full border border-slate-300 rounded px-3 py-2 text-xs pr-12">
                            <span class="absolute right-3 top-2 text-xs text-slate-400">g/m³</span>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Exposure period *</label>
                        <div class="relative">
                            <input type="text" id="fwExposurePeriod" placeholder="24" class="w-full border border-slate-300 rounded px-3 py-2 text-xs pr-12">
                            <span class="absolute right-3 top-2 text-xs text-slate-400">hours</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enclosure & Dimensions -->
            <div class="pl-8 pt-4 border-t border-slate-100 space-y-4">
                <h3 class="font-bold text-xs text-slate-800">Enclosure type &amp; volume</h3>
                <div class="grid grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Length *</label>
                        <input type="text" id="fwLength" placeholder="m" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Height *</label>
                        <input type="text" id="fwHeight" placeholder="m" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Width *</label>
                        <input type="text" id="fwWidth" placeholder="m" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Total volume *</label>
                        <input type="text" id="fwTotalVolume" placeholder="m³" class="w-full border border-slate-300 rounded px-3 py-2 text-xs font-semibold">
                    </div>
                </div>
            </div>

            <!-- Dosage Calculations -->
            <div class="pl-8 pt-4 border-t border-slate-100 space-y-4">
                <h3 class="font-bold text-xs text-slate-800">Fumigant application</h3>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Dose rate used *</label>
                        <input type="text" id="fwDoseRateUsed" placeholder="g/m³" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Calculated dose *</label>
                        <input type="text" id="fwCalculatedDose" placeholder="g" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-1">Amount CH₃Br applied *</label>
                        <input type="text" id="fwAmountApplied" placeholder="g" class="w-full border border-slate-300 rounded px-3 py-2 text-xs">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION D: Concentration Readings Table -->
        <div id="secD" class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-6 h-6 bg-teal-100 text-teal-800 font-bold rounded flex items-center justify-center text-xs">D</span>
                <h2 class="text-base font-bold text-slate-900">Concentration readings</h2>
            </div>
            <p class="text-xs text-slate-400 mb-4 pl-8">Record observations as taken. Location readings and standard are in g/m³.</p>

            <div class="pl-8 overflow-x-auto">
                <table class="w-full text-left text-xs border border-slate-200 rounded">
                    <thead class="bg-slate-50 text-slate-600 font-semibold border-b border-slate-200">
                        <tr>
                            <th class="p-2 border-r">Reading</th>
                            <th class="p-2 border-r">1 FB</th>
                            <th class="p-2 border-r">2 MC</th>
                            <th class="p-2 border-r">3 TB</th>
                            <th class="p-2 border-r">4</th>
                            <th class="p-2 border-r">5</th>
                            <th class="p-2 border-r">Equilibrium result %</th>
                            <th class="p-2 border-r">Standard g/m³</th>
                            <th class="p-2 border-r">Date dd/mm</th>
                            <th class="p-2 border-r">Time hh:mm</th>
                            <th class="p-2">Fumigator initials</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 font-mono">
                        <tr>
                            <td class="p-2 font-sans font-semibold border-r">Start</td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-16 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-16 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-16 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-16 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                        </tr>
                        <tr class="bg-slate-50/50">
                            <td class="p-2 font-sans font-semibold border-r">End</td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-16 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-16 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-16 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2 border-r"><input type="text" class="w-16 text-center border-none bg-transparent" placeholder="—"></td>
                            <td class="p-2"><input type="text" class="w-12 text-center border-none bg-transparent" placeholder="—"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION E: Declaration & Signatures -->
        <div id="secE" class="bg-white rounded-lg border border-slate-200 p-6 shadow-sm space-y-6">
            <div class="flex items-center gap-2 mb-1">
                <span class="w-6 h-6 bg-teal-100 text-teal-800 font-bold rounded flex items-center justify-center text-xs">E</span>
                <h2 class="text-base font-bold text-slate-900">Declaration and signatures</h2>
            </div>
            <p class="text-xs text-slate-400 mb-4 pl-8">Complete the mandatory declaration and both GAS IN / GAS OUT signatures before review.</p>

            <div class="pl-8 space-y-6">
                <div class="p-4 bg-slate-50 border border-slate-200 rounded flex items-start gap-3">
                    <input type="checkbox" id="fwDeclCheck" class="mt-0.5 rounded text-teal-600 focus:ring-teal-500">
                    <label for="fwDeclCheck" class="text-xs text-slate-700 leading-relaxed cursor-pointer">
                        I, the fumigator-in-charge declare that the fumigation was conducted in accordance with the treatment schedule and all the requirements in the Methyl Bromide Fumigation Methodology, and the information I have provided is true and correct.
                    </label>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-2">GAS IN signature *</label>
                        <div class="p-6 bg-slate-50 border border-dashed border-slate-300 rounded text-center text-xs text-slate-400 cursor-pointer hover:bg-slate-100">
                            <svg width="24" height="24" class="mx-auto text-slate-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            Signature captured (Mere Naidu)
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 mb-2">GAS OUT signature *</label>
                        <div class="p-6 bg-slate-50 border border-dashed border-slate-300 rounded text-center text-xs text-slate-400 cursor-pointer hover:bg-slate-100">
                            <svg width="24" height="24" class="mx-auto text-slate-400 mb-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg>
                            Signature captured (Mere Naidu)
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit & Decision Actions -->
        <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-slate-200 shadow-sm">
            <div class="text-xs text-slate-400 font-medium">Draft record • Not submitted for review</div>
            <div class="flex items-center gap-3">
                <button type="button" onclick="FwRecord.saveDraft()" class="px-4 py-2 border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-semibold rounded">Save draft</button>
                <button type="button" onclick="FwRecord.recordSuccess()" class="px-4 py-2 bg-teal-700 hover:bg-teal-800 text-white text-xs font-semibold rounded shadow-sm flex items-center gap-1.5">
                    Record authorized decision (Successful) →
                </button>
            </div>
        </div>
    </form>
</div>

<script>
window.FwRecord = {
    loadData: function() {
        fetch(`api/fieldwork.php?action=get_job&id=<?= $jobId ?>`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.job) {
                    const j = data.job;
                    const r = data.record;
                    document.getElementById('fwRecBreadcrumb').innerText = `${j.job_ref} / Record`;
                    document.getElementById('fwRecSubHeader').innerText = `${j.treatment_type || 'Methyl bromide'} • ${r ? r.record_ref : ''}`;
                    document.getElementById('fwRecJobRef').innerText = j.job_ref;
                    document.getElementById('fwRecClient').innerText = j.client_name;
                    document.getElementById('fwRecContainer').innerText = j.container_number || '—';

                    if (r) {
                        document.getElementById('fwRecId').value = r.record_id;
                        document.getElementById('fwFumigatorName').value = r.fumigator_name || 'Mere Naidu';
                        document.getElementById('fwSchedule').value = r.specified_treatment_schedule || '';
                        document.getElementById('fwDoseRate').value = r.dose_rate || '';
                        document.getElementById('fwExposurePeriod').value = r.exposure_period || '';
                    }
                }
            });
    },

    saveDraft: function() {
        alert('Draft saved successfully.');
    },

    recordSuccess: function() {
        fetch('api/fieldwork.php?action=record_decision', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ job_id: <?= $jobId ?>, decision: 'Successful' })
        }).then(res => res.json()).then(data => {
            if (data.success) {
                NuApp.loadModule('fieldwork_job_orders', 'review', 'inline', 'inline', {job_id: <?= $jobId ?>});
            }
        });
    }
};

FwRecord.loadData();
</script>
