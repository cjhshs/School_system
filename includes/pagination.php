<?php
// Pagination Helper
class Pagination {
    public $current_page;
    public $per_page;
    public $total_items;
    public $total_pages;
    public $offset;

    public function __construct($total_items, $per_page = 20, $current_page = 1) {
        $this->total_items = intval($total_items);
        $this->per_page = max(1, intval($per_page));
        $this->current_page = max(1, intval($current_page));
        $this->total_pages = ceil($this->total_items / $this->per_page);
        $this->current_page = min($this->current_page, max(1, $this->total_pages));
        $this->offset = ($this->current_page - 1) * $this->per_page;
    }

    public function has_previous() {
        return $this->current_page > 1;
    }

    public function has_next() {
        return $this->current_page < $this->total_pages;
    }

    public function previous_page() {
        return $this->has_previous() ? $this->current_page - 1 : 1;
    }

    public function next_page() {
        return $this->has_next() ? $this->current_page + 1 : $this->total_pages;
    }

    public function render($base_url = '') {
        if ($this->total_pages <= 1) return '';
        
        $html = '<nav aria-label="Pagination"><ul class="pagination justify-content-center">';
        
        $html .= '<li class="page-item' . ($this->has_previous() ? '' : ' disabled') . '">';
        $html .= '<a class="page-link" href="' . $base_url . '&page=' . $this->previous_page() . '">&laquo;</a></li>';
        
        $start = max(1, $this->current_page - 2);
        $end = min($this->total_pages, $this->current_page + 2);
        
        if ($start > 1) {
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=1">1</a></li>';
            if ($start > 2) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
        
        for ($i = $start; $i <= $end; $i++) {
            $active = $i == $this->current_page ? ' active' : '';
            $html .= '<li class="page-item' . $active . '"><a class="page-link" href="' . $base_url . '&page=' . $i . '">' . $i . '</a></li>';
        }
        
        if ($end < $this->total_pages) {
            if ($end < $this->total_pages - 1) $html .= '<li class="page-item disabled"><span class="page-link">...</span></li>';
            $html .= '<li class="page-item"><a class="page-link" href="' . $base_url . '&page=' . $this->total_pages . '">' . $this->total_pages . '</a></li>';
        }
        
        $html .= '<li class="page-item' . ($this->has_next() ? '' : ' disabled') . '">';
        $html .= '<a class="page-link" href="' . $base_url . '&page=' . $this->next_page() . '">&raquo;</a></li>';
        
        $html .= '</ul></nav>';
        $html .= '<p class="text-center text-muted small">Showing ' . ($this->offset + 1) . '-' . min($this->offset + $this->per_page, $this->total_items) . ' of ' . $this->total_items . '</p>';
        
        return $html;
    }
}
