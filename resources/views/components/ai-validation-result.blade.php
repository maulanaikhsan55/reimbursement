@props(['results'])

<div class="ai-validation-result">
@if(!$results->isEmpty())
    @php
        $ocrValidasi = $results->where('jenis_validasi', 'ocr')->first();
        $ocrData = null;
        if ($ocrValidasi) {
            $ocrData = is_string($ocrValidasi->hasil_ocr) ? json_decode($ocrValidasi->hasil_ocr, true) : $ocrValidasi->hasil_ocr;
        }
    @endphp

    @if($ocrData)
    <!-- Professional AI Audit Summary -->
    <div class="ai-audit-shell" style="margin-bottom: 1.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div class="ai-audit-head" style="padding: 1rem 1.25rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 0.625rem;">
                <div style="color: #4f46e5;">
                    <x-icon name="cpu" class="w-5 h-5" />
                </div>
                <h3 class="ai-audit-title" style="margin: 0; font-size: 0.9375rem; font-weight: 700; color: #1e293b; letter-spacing: -0.01em;">AI Auditor Real-time</h3>
            </div>
            
            @php
                $riskScore = $ocrData['fraud_risk_score'] ?? 0;
                $riskColor = $riskScore < 30 ? '#10b981' : ($riskScore < 70 ? '#f59e0b' : '#ef4444');
                $confidence = $ocrData['confidence_score'] ?? 0;
                $category = $ocrData['suggested_category'] ?? 'Lainnya';
            @endphp
            <div class="ai-audit-metrics" style="display: flex; align-items: center; gap: 1rem;">
                <div style="text-align: right;">
                    <div style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Confidence</div>
                    <div style="font-size: 0.8125rem; font-weight: 700; color: #475569;">{{ $confidence }}%</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Kategori</div>
                    <div style="font-size: 0.8125rem; font-weight: 700; color: #4f46e5;">{{ $category }}</div>
                </div>
                <div style="padding-left: 0.75rem; border-left: 1px solid #e2e8f0; text-align: right;">
                    <div style="font-size: 0.65rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">Fraud Risk</div>
                    <div style="font-size: 0.875rem; font-weight: 800; color: {{ $riskColor }};">{{ $riskScore }}/100</div>
                </div>
            </div>
        </div>

        <div class="ai-audit-body" style="padding: 1.25rem; display: grid; grid-template-columns: 1.2fr 1fr; gap: 1.5rem;">
            <!-- Simple Item Breakdown -->
            <div class="ai-audit-items">
                <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.75rem;">Itemized Receipt</div>
                @if(isset($ocrData['items']) && count($ocrData['items']) > 0)
                    <div style="display: flex; flex-direction: column; gap: 0.4rem;">
                        @foreach($ocrData['items'] as $item)
                            <div style="display: flex; justify-content: space-between; font-size: 0.8125rem; padding-bottom: 0.25rem;">
                                <span style="color: #334155; font-weight: 500;">{{ $item['qty'] ?? 1 }}x {{ $item['name'] }}</span>
                                <span style="color: #64748b; font-weight: 600;">{{ isset($item['price']) ? number_format($item['price'], 0, ',', '.') : '-' }}</span>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p style="font-size: 0.75rem; color: #94a3b8; font-style: italic;">No items detected.</p>
                @endif
            </div>

            <!-- Audit & Policy -->
            <div class="ai-audit-notes" style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <div style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem;">Audit & Behavioral Notes</div>
                    @php
                        $notes = $ocrData['sanity_check_notes'] ?? 'Transaction appears valid.';
                        $parts = explode(' [ANOMALI PERILAKU]: ', $notes);
                        $baseNote = $parts[0];
                        $anomalies = isset($parts[1]) ? explode('. ', $parts[1]) : [];
                    @endphp
                    
                    <p style="margin: 0; font-size: 0.8125rem; color: #475569; line-height: 1.4;">
                        {{ $baseNote }}
                    </p>

                    @if(!empty($anomalies))
                        <div style="margin-top: 0.5rem; display: flex; flex-direction: column; gap: 0.25rem;">
                            @foreach($anomalies as $anomaly)
                                @if(trim($anomaly))
                                    <div style="font-size: 0.75rem; color: #ef4444; font-weight: 600; display: flex; align-items: flex-start; gap: 0.25rem;">
                                        <span>•</span>
                                        <span>{{ trim($anomaly) }}</span>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                    
                    @if(isset($ocrData['accounting_split']) && count($ocrData['accounting_split']) > 0)
                        <div style="margin-top: 0.75rem; padding: 0.6rem; background: #f1f5f9; border-radius: 6px; border: 1px dashed #cbd5e1;">
                            <div style="font-size: 0.65rem; font-weight: 700; color: #475569; text-transform: uppercase; margin-bottom: 0.25rem;">Accounting Split</div>
                            @foreach($ocrData['accounting_split'] as $split)
                                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: #1e293b;">
                                    <span>{{ $split['category'] }}</span>
                                    <span style="font-weight: 600;">Rp {{ number_format($split['amount'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if(isset($ocrData['policy_violations']) && count($ocrData['policy_violations']) > 0)
                    <div style="padding: 0.75rem; background: #fff1f2; border-radius: 8px; border: 1px solid #fee2e2;">
                        <div style="font-size: 0.65rem; font-weight: 800; color: #be123c; text-transform: uppercase; margin-bottom: 0.25rem;">Policy Violations</div>
                        <ul style="margin: 0; padding-left: 1rem; color: #9f1239; font-size: 0.75rem; font-weight: 600;">
                            @foreach($ocrData['policy_violations'] as $violation)
                                <li>{{ is_array($violation) ? ($violation['item'] ?? $violation['reason'] ?? 'Violation') : $violation }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- Compact Validation Grid -->
    <div class="ai-validation-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 0.75rem;">
        @foreach($results as $validasi)
            @php
                $labelMap = [
                    'ocr' => 'OCR Extraction',
                    'duplikasi' => 'Duplicate Check',
                    'vendor' => 'Merchant Match',
                    'nominal' => 'Amount Verification',
                    'tanggal' => 'Date Match',
                    'anomali' => 'Behavioral AI',
                    'pajak' => 'Tax Validation',
                    'sekuensial' => 'Sequence Check'
                ];
                $displayLabel = $labelMap[$validasi->jenis_validasi] ?? ucfirst($validasi->jenis_validasi);
                
                $status = $validasi->status->value;
                $isPass = $status === 'valid';
                $isWarning = $status === 'invalid' && !$validasi->is_blocking;
                $isFail = $status === 'invalid' && $validasi->is_blocking;
                
                $badgeColor = $isPass ? '#10b981' : ($isWarning ? '#f59e0b' : '#ef4444');
                $badgeBg = $isPass ? '#f0fdf4' : ($isWarning ? '#fffbeb' : '#fef2f2');
            @endphp
            
            <div class="ai-validation-card" style="padding: 0.875rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; display: flex; flex-direction: column; gap: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 0.7rem; font-weight: 700; color: #64748b; text-transform: uppercase;">{{ $displayLabel }}</span>
                    <span style="padding: 0.2rem 0.5rem; background: {{ $badgeBg }}; color: {{ $badgeColor }}; border-radius: 4px; font-size: 0.65rem; font-weight: 800; text-transform: uppercase;">
                        {{ $isPass ? 'Passed' : ($isWarning ? 'Warning' : 'Failed') }}
                    </span>
                </div>
                
                <p style="margin: 0; font-size: 0.75rem; color: #475569; line-height: 1.3;">{{ $validasi->pesan_validasi }}</p>
                
                @if($validasi->confidence_score !== null)
                    <div style="margin-top: auto; display: flex; align-items: center; gap: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed #f1f5f9;">
                        <div style="flex: 1; height: 4px; background: #f1f5f9; border-radius: 2px; overflow: hidden;">
                            <div style="height: 100%; width: {{ $validasi->confidence_score }}%; background: {{ $badgeColor }};"></div>
                        </div>
                        <span style="font-size: 0.65rem; font-weight: 700; color: #94a3b8;">{{ $validasi->confidence_score }}%</span>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="ai-validation-empty" style="padding: 1.5rem; background: #f8fafc; border: 1px dashed #e2e8f0; border-radius: 12px; text-align: center;">
        <p style="margin: 0; font-size: 0.8125rem; color: #94a3b8;">Sistem sedang memproses validasi AI...</p>
    </div>
@endif
</div>
