import { useRef, useState } from 'react';
import Link from 'next/link';
import { useRouter } from 'next/router';

export default function MainMenu({ items = [] }) {
  const router = useRouter();
  const scrollContainerRef = useRef(null);
  const [isDragging, setIsDragging] = useState(false);
  const [startX, setStartX] = useState(0);
  const [scrollLeft, setScrollLeft] = useState(0);

  if (!items.length) return null;

  const handleMouseDown = (e) => {
    setIsDragging(true);
    setStartX(e.pageX - scrollContainerRef.current.offsetLeft);
    setScrollLeft(scrollContainerRef.current.scrollLeft);
    // Prevent text selection during drag
    e.preventDefault();
    document.onselectstart = () => false;
  };

  const handleMouseLeave = () => {
    setIsDragging(false);
    document.onselectstart = null;
  };

  const handleMouseUp = () => {
    setIsDragging(false);
    document.onselectstart = null;
  };

  const handleMouseMove = (e) => {
    if (!isDragging) return;
    e.preventDefault();
    const x = e.pageX - scrollContainerRef.current.offsetLeft;
    const walk = (x - startX) * 1;
    scrollContainerRef.current.scrollLeft = scrollLeft - walk;
  };

  const handleTouchStart = (e) => {
    setIsDragging(true);
    setStartX(e.touches[0].pageX - scrollContainerRef.current.offsetLeft);
    setScrollLeft(scrollContainerRef.current.scrollLeft);
  };

  const handleTouchMove = (e) => {
    if (!isDragging) return;
    const x = e.touches[0].pageX - scrollContainerRef.current.offsetLeft;
    const walk = (x - startX) * 1;
    scrollContainerRef.current.scrollLeft = scrollLeft - walk;
  };

  const handleTouchEnd = () => {
    setIsDragging(false);
  };

  return (
    <nav className="main-menu">
      <div
        className="main-menu__scroll"
        ref={scrollContainerRef}
        onMouseDown={handleMouseDown}
        onMouseLeave={handleMouseLeave}
        onMouseUp={handleMouseUp}
        onMouseMove={handleMouseMove}
        onTouchStart={handleTouchStart}
        onTouchMove={handleTouchMove}
        onTouchEnd={handleTouchEnd}
      >
        <ul className="main-menu__list">
          {items.map(item => {
            let href = item.url;
            try {
              const url = new URL(item.url);
              href = url.pathname + url.search;
            } catch (e) {
              if (!href.startsWith('/')) href = '/' + href;
            }

            const currentPath = router.asPath.split('?')[0];
            const normalize = (str) => str.replace(/\/$/, '');
            const isActive = normalize(href) === normalize(currentPath) || 
                            (href === '/' && currentPath === '') ||
                            (href !== '/' && currentPath.startsWith(normalize(href)));

            const linkClass = `main-menu__link ${isActive ? 'main-menu__link--current' : ''}`;

            return (<li key={item.id} className="main-menu__item">
              <Link href={href} className="main-menu__link" legacyBehavior>
                <a href={href} className={linkClass}>{item.title}</a>
              </Link>
            </li>)
          })}
        </ul>
      </div>

      <style jsx>{`
        .main-menu {
          position: relative;
          margin-bottom: 1rem;
          border-bottom: 1px solid #ddd;
        }

        .main-menu::before,
        .main-menu::after {
          display: block;
          content: '';
          position: absolute;
          top: 0;
          height: 100%;
          z-index: 2;
          pointer-events: none; /* optional: allow clicking through gradients */
        }

        .main-menu::before {
          left: 0;
          width: 1rem;
          background: linear-gradient(to right, #fff, transparent);
        }

        .main-menu::after {
          right: 0;
          width: 4rem;
          background: linear-gradient(to right, transparent, #fff);
        }
        .main-menu__scroll {
          overflow-x: auto;
          cursor: grab;
          scrollbar-width: none;          /* Firefox */
          -ms-overflow-style: none;       /* IE/Edge */
          user-select: none;              /* Disable text selection */
        }
        .main-menu__scroll::-webkit-scrollbar {
          display: none;                  /* Chrome, Safari, Opera */
        }
        .main-menu__scroll:active {
          cursor: grabbing;
        }
        .main-menu__list {
          display: flex;
          flex-wrap: nowrap;
          list-style: none;
          margin: 0;
          padding: 0;
          align-items: center;
          justify-content: flex-start;
        }
        .main-menu__item {
          margin: 0;
          padding: 0;
          white-space: nowrap;
        }
        .main-menu__link {
          display: inline-flex;
          text-decoration: none;
          padding: 1.5rem 1rem;
          color: #000;
          font-weight: 600;
          white-space: nowrap;
        }
        .main-menu__link--current {
          padding-bottom: calc(1.5rem - 2px);
          border-bottom: 2px solid black;
        }
        .main-menu__link:first-child {
          padding-left: 1rem;
        }
        .main-menu__link:hover {
          background-color: #f6f6f6;
        }
        @media (max-width: 768px) {
          .main-menu__list {
            gap: 1rem;
          }
        }
      `}</style>
    </nav>
  );
}