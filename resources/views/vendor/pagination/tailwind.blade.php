@if ($paginator->hasPages())
    @once
        <style>
        .ppid-pager {
            display:flex;
            flex-wrap:wrap;
            align-items:center;
            justify-content:space-between;
            gap:14px;
            width:100%;
            margin:22px 0 8px;
            font-family:inherit;
        }
        .ppid-pager .ppid-pager-summary {
            margin:0;
            color:#617168;
            font-size:13px;
            line-height:1.7;
        }
        .ppid-pager .ppid-pager-items {
            display:flex;
            align-items:center;
            flex-wrap:wrap;
            gap:6px;
            margin:0;
            padding:0;
            list-style:none;
        }
        .ppid-pager .ppid-pager-item {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            box-sizing:border-box;
            min-width:40px;
            min-height:40px;
            padding:8px 12px;
            border:1px solid #dce5df;
            border-radius:9px;
            background:#fff;
            color:#43594b;
            font-size:14px;
            font-weight:500;
            line-height:1.4;
            text-decoration:none;
        }
        .ppid-pager a.ppid-pager-item:hover {
            background:#eef5f0;
            border-color:#b4ccbd;
        }
        .ppid-pager .ppid-pager-item[aria-current=page] {
            background:#edf5f0;
            border-color:#99baa6;
            color:#20573d;
            font-weight:700;
        }
        .ppid-pager .ppid-pager-item[aria-disabled=true] {
            background:#f7f9f8;
            color:#758078;
        }
        .ppid-pager a:focus-visible {
            outline:3px solid #719e83;
            outline-offset:3px;
        }
        .ppid-pager .ppid-pager-gap {
            padding:8px 4px;
            color:#68766d;
        }
        @media(max-width:600px) {
            .ppid-pager {
                justify-content:center;
            }
            .ppid-pager .ppid-pager-summary {
                width:100%;
                text-align:center;
            }
            .ppid-pager .ppid-pager-items {
                justify-content:center;
            }
            .ppid-pager .ppid-pager-item {
                min-width:44px;
                min-height:44px;
            }
        }
        </style>
    @endonce

    <nav class="ppid-pager" aria-label="Navigasi halaman">
        <p class="ppid-pager-summary">
            @if (method_exists($paginator, 'total'))
                {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }}
                dari {{ $paginator->total() }} data
            @else
                Halaman {{ $paginator->currentPage() }}
            @endif
        </p>

        <ul class="ppid-pager-items">
            <li>
                @if ($paginator->onFirstPage())
                    <span class="ppid-pager-item" aria-disabled="true">
                        Sebelumnya
                    </span>
                @else
                    <a class="ppid-pager-item"
                       href="{{ $paginator->previousPageUrl() }}"
                       rel="prev">Sebelumnya</a>
                @endif
            </li>

            @if (method_exists($paginator, 'lastPage'))
                @php
                    $pagerCurrent = $paginator->currentPage();
                    $pagerLast = $paginator->lastPage();
                    $pagerNumbers = [1, $pagerLast];

                    for (
                        $pagerNumber = max(1, $pagerCurrent - 1);
                        $pagerNumber <= min($pagerLast, $pagerCurrent + 1);
                        $pagerNumber++
                    ) {
                        $pagerNumbers[] = $pagerNumber;
                    }

                    $pagerNumbers = array_unique($pagerNumbers);
                    sort($pagerNumbers);
                    $pagerPrevious = 0;
                @endphp

                @foreach ($pagerNumbers as $pagerNumber)
                    @if ($pagerPrevious && $pagerNumber > $pagerPrevious + 1)
                        <li class="ppid-pager-gap" aria-hidden="true">…</li>
                    @endif

                    <li>
                        @if ($pagerNumber === $pagerCurrent)
                            <span class="ppid-pager-item"
                                  aria-current="page"
                                  aria-label="Halaman {{ $pagerNumber }}">
                                {{ $pagerNumber }}
                            </span>
                        @else
                            <a class="ppid-pager-item"
                               href="{{ $paginator->url($pagerNumber) }}"
                               aria-label="Buka halaman {{ $pagerNumber }}">
                                {{ $pagerNumber }}
                            </a>
                        @endif
                    </li>

                    @php $pagerPrevious = $pagerNumber; @endphp
                @endforeach
            @endif

            <li>
                @if ($paginator->hasMorePages())
                    <a class="ppid-pager-item"
                       href="{{ $paginator->nextPageUrl() }}"
                       rel="next">Berikutnya</a>
                @else
                    <span class="ppid-pager-item" aria-disabled="true">
                        Berikutnya
                    </span>
                @endif
            </li>
        </ul>
    </nav>
@endif