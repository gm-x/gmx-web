<?php
namespace GameX\Core\Pagination;

use \Twig_Extension;
use \Twig_SimpleFunction;

class Extention extends Twig_Extension {
    /**
     * @return array
     */
    public function getFunctions() {
        return [
            new Twig_SimpleFunction(
                'render_pagination',
                [$this, 'render'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    public function render(Pagination $pagination) {
        ob_start();
        ?>
        <nav>
            <ul class="pagination">
            <?php if ($pagination->getPage() > 5): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $pagination->getPageUrl($pagination->getPage()); ?>" aria-label="Previous">
                        <span aria-hidden="true">&laquo;</span>
                        <span class="sr-only">Previous</span>
                    </a>
                </li>
                <li class="page-item"><a class="page-link" href=<?= $pagination->getPageUrl(1); ?>">&nbsp;1&nbsp;</a></li>
            <?php endif; ?>
            <?php for($i = $pagination->getStartPage(); $i < $pagination->getEndPage(); $i++): ?>
                <li class="page-item <?= ($i == $pagination->getPage()) ? 'active' : ''; ?>">
                    <a class="page-link" href="<?= $pagination->getPageUrl($i + 1); ?>"><?= $i + 1; ?></a>
                </li>
                <?php endfor; ?>
                <?php if ($pagination->getPage() < $pagination->getPagesCount() - 5): ?>
                <li class="page-item">
                    <a class="page-link" href="<?= $pagination->getPageUrl($pagination->getPagesCount()); ?>">&nbsp;<?= $pagination->getPagesCount(); ?>&nbsp;</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="<?= $pagination->getPageUrl($pagination->getPage() + 2); ?>" aria-label="Next">
                        <span aria-hidden="true">&raquo;</span>
                        <span class="sr-only">Next</span>
                    </a>
                </li>
                <?php endif; ?>
            </ul>
        </nav>
        <?php
        return ob_get_clean();
    }
}
