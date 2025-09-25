// Enhanced dynamic content loader for the theme
(function(){
  async function fetchContent(){
    try{
      // Try public endpoint first (for exported themes)
      let res = await fetch('/api/content-public');
      if(!res.ok) {
        // Fallback to regular endpoint (might work if no auth required)
        res = await fetch('/api/content');
      }
      if(!res.ok) return null;
      return await res.json();
    }catch(e){console.warn('content fetch failed',e);return null}
  }

  function injectCSS(css) {
    const style = document.createElement('style');
    style.textContent = css;
    document.head.appendChild(style);
  }

  function applyColors(siteSettings) {
    try {
      if (!siteSettings) return;

      const root = document.documentElement;

      // Apply primary color
      if (siteSettings.primaryColor) {
        root.style.setProperty('--primary-color', siteSettings.primaryColor);
        root.style.setProperty('--wp--preset--color--primary', siteSettings.primaryColor);
      }

      // Apply secondary color
      if (siteSettings.secondaryColor) {
        root.style.setProperty('--secondary-color', siteSettings.secondaryColor);
        root.style.setProperty('--wp--preset--color--secondary', siteSettings.secondaryColor);
      }

      // Apply tertiary color
      if (siteSettings.tertiaryColor) {
        root.style.setProperty('--tertiary-color', siteSettings.tertiaryColor);
        root.style.setProperty('--wp--preset--color--tertiary', siteSettings.tertiaryColor);
      }

      // Apply navbar background color
      if (siteSettings.navbarBackground) {
        console.log('Applying navbar background color:', siteSettings.navbarBackground);

        const navbarSelectors = [
          '.navbar',
          '.main-header',
          '#header',
          '.top-header',
          '.header',
          '.l-header',
          '.top-bar-container',
          '.positioned .top-bar-container',
          '.top-bar',
          '.positioned .top-bar-header'
        ];

        navbarSelectors.forEach(selector => {
          const elements = document.querySelectorAll(selector);
          console.log(`Found ${elements.length} elements for navbar selector: ${selector}`);
          elements.forEach(element => {
            element.style.backgroundColor = siteSettings.navbarBackground;
            element.style.setProperty('background-color', siteSettings.navbarBackground, 'important');
          });
        });

        // Inject CSS to override hardcoded styles
        const navbarCSS = `
          .positioned .top-bar-header,
          .positioned .top-bar-container,
          .l-header,
          .top-bar {
            background-color: ${siteSettings.navbarBackground} !important;
          }
        `;
        injectCSS(navbarCSS);
      }
    } catch(e) { console.warn('Color application failed:', e); }
  }

  function applyLogo(siteSettings) {
    try {
      if (!siteSettings?.logo) {
        console.log('No logo in site settings');
        return;
      }

      console.log('Applying logo:', siteSettings.logo);

      const logoSelectors = [
        '.logo img',
        '.site-logo img',
        '.header-logo img',
        '.brand img',
        '.navbar-brand img',
        '#logo img',
        'img.logo',
        'img.logo-sticky'
      ];

      logoSelectors.forEach(selector => {
        const logos = document.querySelectorAll(selector);
        console.log(`Found ${logos.length} elements for selector: ${selector}`);
        logos.forEach(logo => {
          const newSrc = siteSettings.logo.startsWith('/') ? siteSettings.logo : '/' + siteSettings.logo;
          console.log(`Updating logo src from ${logo.src} to ${newSrc}`);
          logo.src = newSrc;
          logo.alt = 'Site Logo';
        });
      });
    } catch(e) { console.warn('Logo application failed:', e); }
  }

  function applyFavicon(siteSettings) {
    try {
      if (!siteSettings?.favicon) return;

      let favicon = document.querySelector('link[rel="icon"], link[rel="shortcut icon"]');
      if (!favicon) {
        favicon = document.createElement('link');
        favicon.rel = 'icon';
        document.head.appendChild(favicon);
      }
      favicon.href = siteSettings.favicon.startsWith('/') ? siteSettings.favicon : '/' + siteSettings.favicon;
    } catch(e) { console.warn('Favicon application failed:', e); }
  }

  function applyFooter(siteSettings) {
    try {
      // Apply footer text
      if (siteSettings.footerText) {
        const footerTextSelectors = [
          '.footer-text',
          '.footer-content p',
          '.footer-description',
          '.footer .copyright',
          '.site-info'
        ];

        footerTextSelectors.forEach(selector => {
          const elements = document.querySelectorAll(selector);
          elements.forEach(el => el.textContent = siteSettings.footerText);
        });
      }

      // Apply footer background image
      if (siteSettings.footerBackgroundImage) {
        const footer = document.querySelector('footer, .footer, #footer, .site-footer');
        if (footer) {
          footer.style.backgroundImage = `url(${siteSettings.footerBackgroundImage.startsWith('/') ? siteSettings.footerBackgroundImage : '/' + siteSettings.footerBackgroundImage})`;
          footer.style.backgroundSize = 'cover';
          footer.style.backgroundPosition = 'center';
        }
      }
    } catch(e) { console.warn('Footer application failed:', e); }
  }

  function applyServices(services){
    try{
      const nodes = document.querySelectorAll('.vc_custom_1468855171122, .vc_custom_1468854835303, .vc_custom_1468854850054');
      if(nodes && services && services.items && services.items.length>=3){
        services.items.slice(0,3).forEach((s,i)=>{
          const node = nodes[i];
          if(!node) return;
          const img = node.querySelector('img');
          const h4 = node.querySelector('h4.wd-title-element');
          const p = node.querySelector('p');
          if(img && s.image) img.src = s.image.startsWith('/')? s.image : s.image;
          if(h4) h4.textContent = s.title;
          if(p) p.textContent = s.description;
        });
      }
    }catch(e){console.warn('Services application failed:', e)}
  }

  function applyLatestNews(content) {
    try {
      if (!content.latestNews || !content.latestNews.length) return;

      // Create news cards section if it doesn't exist
      let newsSection = document.querySelector('.latest-news-section');
      if (!newsSection) {
        const insertAfter = document.querySelector('.services-section, #services, .about-section');
        if (insertAfter) {
          newsSection = document.createElement('section');
          newsSection.className = 'latest-news-section';
          newsSection.style.cssText = 'padding: 60px 0; background: #f8f9fa;';
          newsSection.innerHTML = `
            <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
              <h2 style="text-align: center; margin-bottom: 40px; color: #2c3e50; font-size: 2.5rem;">Latest News</h2>
              <div class="news-cards" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;"></div>
            </div>
          `;
          insertAfter.parentNode.insertBefore(newsSection, insertAfter.nextSibling);
        }
      }

      const newsContainer = newsSection?.querySelector('.news-cards');
      if (newsContainer) {
        newsContainer.innerHTML = content.latestNews.map(news => `
          <div class="news-card" style="background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;">
            ${news.image ? `<img src="${news.image}" alt="${news.title}" style="width: 100%; height: 200px; object-fit: cover;">` : ''}
            <div style="padding: 20px;">
              <h3 style="color: #2c3e50; margin-bottom: 10px; font-size: 1.4rem; line-height: 1.3;">${news.title}</h3>
              <p style="color: #6c757d; margin-bottom: 15px; line-height: 1.6;">${news.excerpt}</p>
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span class="news-date" style="color: #95a5a6; font-size: 0.9rem;">${news.date}</span>
                ${news.link ? `<a href="${news.link}" class="read-more" style="color: var(--primary-color, #FFD200); text-decoration: none; font-weight: 600; padding: 8px 16px; border: 2px solid var(--primary-color, #FFD200); border-radius: 6px; transition: all 0.3s ease;">Read More</a>` : ''}
              </div>
            </div>
          </div>
        `).join('');

        // Add hover effects
        const newsCards = newsContainer.querySelectorAll('.news-card');
        newsCards.forEach(card => {
          card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
          });
          card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
          });
        });
      }
    } catch(e) { console.warn('Latest news application failed:', e); }
  }

  function applyLatestProjects(content) {
    try {
      if (!content.latestProjects || !content.latestProjects.length) return;

      // Create projects section if it doesn't exist
      let projectsSection = document.querySelector('.latest-projects-section');
      if (!projectsSection) {
        const insertAfter = document.querySelector('.latest-news-section, .services-section, #services');
        if (insertAfter) {
          projectsSection = document.createElement('section');
          projectsSection.className = 'latest-projects-section';
          projectsSection.style.cssText = 'padding: 60px 0; background: white;';
          projectsSection.innerHTML = `
            <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 0 20px;">
              <h2 style="text-align: center; margin-bottom: 40px; color: #2c3e50; font-size: 2.5rem;">Latest Projects</h2>
              <div class="projects-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;"></div>
            </div>
          `;
          insertAfter.parentNode.insertBefore(projectsSection, insertAfter.nextSibling);
        }
      }

      const projectsContainer = projectsSection?.querySelector('.projects-grid');
      if (projectsContainer) {
        projectsContainer.innerHTML = content.latestProjects.map(project => `
          <div class="project-card" style="background: #f8f9fa; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); overflow: hidden; transition: transform 0.3s ease, box-shadow 0.3s ease;">
            ${project.image ? `<img src="${project.image}" alt="${project.title}" style="width: 100%; height: 220px; object-fit: cover;">` : ''}
            <div style="padding: 25px;">
              <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 15px;">
                <h3 style="color: #2c3e50; margin: 0; font-size: 1.5rem; line-height: 1.3; flex: 1;">${project.title}</h3>
                ${project.category ? `<span class="project-category" style="background: var(--primary-color, #FFD200); color: #2c3e50; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; margin-left: 15px; white-space: nowrap;">${project.category}</span>` : ''}
              </div>
              <p style="color: #6c757d; margin-bottom: 20px; line-height: 1.6;">${project.description}</p>
              ${project.link ? `<a href="${project.link}" class="view-project" style="color: var(--secondary-color, #484939); text-decoration: none; font-weight: 600; padding: 10px 20px; background: var(--primary-color, #FFD200); border-radius: 6px; transition: all 0.3s ease; display: inline-block;">View Project</a>` : ''}
            </div>
          </div>
        `).join('');

        // Add hover effects
        const projectCards = projectsContainer.querySelectorAll('.project-card');
        projectCards.forEach(card => {
          card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = '0 8px 25px rgba(0,0,0,0.15)';
          });
          card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
          });
        });
      }
    } catch(e) { console.warn('Latest projects application failed:', e); }
  }

  async function init(){
    console.log('Dynamic content loader starting...');
    const content = await fetchContent();
    if(!content) {
      console.log('No content fetched');
      return;
    }
    console.log('Content fetched:', content);

    // Apply site settings
    if(content.siteSettings) {
      applyColors(content.siteSettings);
      applyLogo(content.siteSettings);
      applyFavicon(content.siteSettings);
      applyFooter(content.siteSettings);
    }

    // Apply services
    if(content.services) applyServices(content.services);

    // Apply site title
    if(content.siteTitle){
      // Update page title
      const pageTitle = document.querySelector('title');
      if(pageTitle) pageTitle.textContent = content.siteTitle;

      // Update navbar title link
      const titleLink = document.querySelector('h1 a[title]');
      if(titleLink) {
        titleLink.title = content.siteTitle;
        titleLink.setAttribute('title', content.siteTitle);
      }

      // Update logo alt text
      const logoImages = document.querySelectorAll('img.logo, img.logo-sticky');
      logoImages.forEach(img => {
        img.alt = content.siteTitle;
      });

      // Update any other title elements
      const otherTitleEls = document.querySelectorAll('h1 a[rel="home"]');
      otherTitleEls.forEach(el => {
        el.title = content.siteTitle;
      });
    }

    // Apply latest news
    applyLatestNews(content);

    // Apply latest projects
    applyLatestProjects(content);
  }

  // Add a global function to manually refresh content
  window.refreshDynamicContent = init;

  // run after DOM ready
  if(document.readyState==='loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    // Add a small delay to ensure all elements are rendered
    setTimeout(init, 100);
  }
})();
