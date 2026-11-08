<style>
.ds-pagination-nav {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid #e5e7eb;
}
.ds-pagination-summary { margin: 0; color: #4b5563; font-size: .875rem; }
.ds-pagination-wrap { display: flex; align-items: center; justify-content: center; flex-wrap: wrap; gap: 6px; }
.ds-pagination-nav .ds-page-btn, .ds-pagination-nav .ds-page-ellipsis {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 36px; min-height: 36px; padding: 6px 10px;
    font-size: .875rem; font-weight: 600; line-height: 1.25;
    border-radius: 8px; text-decoration: none; box-sizing: border-box;
}
.ds-pagination-nav .ds-page-btn { background: #fff; color: #374151; border: 1px solid #d1d5db; }
.ds-pagination-nav a.ds-page-btn:hover { background: #f3f4f6; border-color: #9ca3af; }
.ds-pagination-nav .ds-page-btn.is-active { background: var(--primary-color, #2563eb); color: #fff; border-color: var(--primary-color, #2563eb); }
.ds-pagination-nav .ds-page-btn.is-disabled { color: #6b7280; background: #f3f4f6; border-color: #e5e7eb; }
.ds-pagination-nav .ds-page-ellipsis { color: #6b7280; }
.ds-pagination-nav a:focus-visible { outline: 2px solid var(--primary-color, #2563eb); outline-offset: 3px; }
@media (max-width: 640px) {
    .ds-pagination-nav { justify-content: center; text-align: center; }
    .ds-pagination-summary { flex-basis: 100%; }
    .ds-pagination-nav .ds-page-btn { min-width: 44px; min-height: 44px; }
}
</style>
