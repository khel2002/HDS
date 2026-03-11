const TRANSITION_EVENTS = ['transitionend', 'webkitTransitionEnd', 'oTransitionEnd']

class Menu {
  constructor(el, config = {}, _PS = null) {
    this._el = el
    this._animate = config.animate !== false
    this._accordion = config.accordion !== false
    this._closeChildren = Boolean(config.closeChildren)

    this._onOpen = config.onOpen || (() => {})
    this._onOpened = config.onOpened || (() => {})
    this._onClose = config.onClose || (() => {})
    this._onClosed = config.onClosed || (() => {})

    this._psScroll = null
    this._topParent = null
    this._menuBgClass = null

    // ── Collapse/hover state ──────────────────────────────────
    // "collapsed"  = sidebar is in icon-only mode (user pinned it closed)
    // "hover"      = sidebar is temporarily expanded because the mouse is over it
    this._collapsed = false
    this._hovered   = false

    el.classList.add('menu')
    el.classList[this._animate ? 'remove' : 'add']('menu-no-animation')
    el.classList.add('menu-vertical')

    const PerfectScrollbarLib = _PS || window.PerfectScrollbar

    if (PerfectScrollbarLib) {
      this._scrollbar = new PerfectScrollbarLib(el.querySelector('.menu-inner'), {
        suppressScrollX: true,
        wheelPropagation: !Menu._hasClass('layout-menu-fixed layout-menu-fixed-offcanvas')
      })

      window.Helpers.menuPsScroll = this._scrollbar
    } else {
      el.querySelector('.menu-inner').classList.add('overflow-auto')
    }

    // Detect and store bg-color class
    const menuClassList = el.classList
    for (let i = 0; i < menuClassList.length; i++) {
      if (menuClassList[i].startsWith('bg-')) {
        this._menuBgClass = menuClassList[i]
      }
    }
    el.setAttribute('data-bg-class', this._menuBgClass)

    // Restore persisted collapse state before binding events
    this._restoreCollapseState()

    this._bindEvents()

    el.menuInstance = this
  }

  // ─────────────────────────────────────────────────────────────
  // COLLAPSE / HOVER / TOGGLE
  // ─────────────────────────────────────────────────────────────

  /**
   * Collapse the sidebar to icon-only mode and persist choice.
   */
  collapseMenu() {
    this._collapsed = true
    this._hovered   = false

    const root = window.Helpers.ROOT_EL
    root.classList.add('layout-menu-collapsed')
    root.classList.remove('layout-menu-hover')

    this._el.setAttribute('data-collapsed', 'true')
    this._saveCollapseState(true)

    // Rotate the toggle icon
    const toggleIcon = this._el.querySelector('.layout-menu-toggle i')
    if (toggleIcon) toggleIcon.style.transform = 'rotate(180deg)'

    this._updateScrollbar()
  }

  /**
   * Expand the sidebar to full mode and persist choice.
   */
  expandMenu() {
    this._collapsed = false
    this._hovered   = false

    const root = window.Helpers.ROOT_EL
    root.classList.remove('layout-menu-collapsed')
    root.classList.remove('layout-menu-hover')

    this._el.removeAttribute('data-collapsed')
    this._saveCollapseState(false)

    // Reset toggle icon
    const toggleIcon = this._el.querySelector('.layout-menu-toggle i')
    if (toggleIcon) toggleIcon.style.transform = ''

    this._updateScrollbar()
  }

  /**
   * Toggle between collapsed and expanded.
   * Called when the user clicks `.layout-menu-toggle`.
   */
  toggleCollapse() {
    if (this._collapsed) {
      this.expandMenu()
    } else {
      this.collapseMenu()
    }
  }

  /**
   * Temporarily expand the sidebar on mouse-enter (hover mode).
   * Only active while the menu is in the collapsed state.
   */
  _onMenuMouseEnter() {
    if (!this._collapsed) return

    this._hovered = true
    window.Helpers.ROOT_EL.classList.add('layout-menu-hover')
    this._updateScrollbar()
  }

  /**
   * Collapse back to icon-only on mouse-leave (end hover mode).
   */
  _onMenuMouseLeave() {
    if (!this._collapsed) return

    this._hovered = false
    window.Helpers.ROOT_EL.classList.remove('layout-menu-hover')
    this._updateScrollbar()
  }

  _saveCollapseState(collapsed) {
    try {
      localStorage.setItem('layoutMenuCollapsed', collapsed ? '1' : '0')
    } catch (_) { /* storage unavailable */ }
  }

  _restoreCollapseState() {
    try {
      const stored = localStorage.getItem('layoutMenuCollapsed')
      if (stored === '1') {
        // Apply collapsed state immediately (before first paint)
        this._collapsed = true
        window.Helpers.ROOT_EL.classList.add('layout-menu-collapsed')
        this._el.setAttribute('data-collapsed', 'true')

        const toggleIcon = this._el.querySelector('.layout-menu-toggle i')
        if (toggleIcon) toggleIcon.style.transform = 'rotate(180deg)'
      }
    } catch (_) { /* storage unavailable */ }
  }

  _updateScrollbar() {
    if (this._scrollbar) {
      // Delay slightly so CSS transition has time to start
      setTimeout(() => this._scrollbar.update(), 300)
    }
  }

  // ─────────────────────────────────────────────────────────────
  // EVENTS
  // ─────────────────────────────────────────────────────────────

  _bindEvents() {
    // ── Submenu toggle (click on .menu-toggle links) ──────────
    this._evntElClick = e => {
      // Track top-level parent for potential use by consumers
      if (e.target.closest('ul') && e.target.closest('ul').classList.contains('menu-inner')) {
        const menuItem = Menu._findParent(e.target, 'menu-item', false)
        if (menuItem) this._topParent = menuItem.childNodes[0]
      }

      // Handle sidebar collapse toggle button
      const sidebarToggle = e.target.closest('.layout-menu-toggle')
      if (sidebarToggle) {
        e.preventDefault()
        this.toggleCollapse()
        return
      }

      // Handle submenu toggles
      const toggleLink = e.target.classList.contains('menu-toggle')
        ? e.target
        : Menu._findParent(e.target, 'menu-toggle', false)

      if (toggleLink) {
        e.preventDefault()
        if (toggleLink.getAttribute('data-hover') !== 'true') {
          this.toggle(toggleLink)
        }
      }
    }

    // On mobile, listen for all clicks on the menu element
    if (window.Helpers.isMobileDevice) {
      this._el.addEventListener('click', this._evntElClick)
    } else {
      // On desktop, always listen so the toggle button works
      this._el.addEventListener('click', this._evntElClick)
    }

    // ── Hover expand / collapse ───────────────────────────────
    this._evntElMouseEnter = () => this._onMenuMouseEnter()
    this._evntElMouseLeave = () => this._onMenuMouseLeave()

    this._el.addEventListener('mouseenter', this._evntElMouseEnter)
    this._el.addEventListener('mouseleave', this._evntElMouseLeave)

    // ── Window resize ─────────────────────────────────────────
    this._evntWindowResize = () => {
      this.update()
      if (this._lastWidth !== window.innerWidth) {
        this._lastWidth = window.innerWidth
        this.update()
      }

      const horizontalMenuTemplate = document.querySelector("[data-template^='horizontal-menu']")
      if (!this._horizontal && !horizontalMenuTemplate) this.manageScroll()
    }
    window.addEventListener('resize', this._evntWindowResize)
  }

  static childOf(c, p) {
    if (c.parentNode) {
      while ((c = c.parentNode) && c !== p);
      return !!c
    }
    return false
  }

  _unbindEvents() {
    if (this._evntElClick) {
      this._el.removeEventListener('click', this._evntElClick)
      this._evntElClick = null
    }

    if (this._evntElMouseEnter) {
      this._el.removeEventListener('mouseenter', this._evntElMouseEnter)
      this._evntElMouseEnter = null
    }

    if (this._evntElMouseLeave) {
      this._el.removeEventListener('mouseleave', this._evntElMouseLeave)
      this._evntElMouseLeave = null
    }

    if (this._evntElMouseOver) {
      this._el.removeEventListener('mouseover', this._evntElMouseOver)
      this._evntElMouseOver = null
    }

    if (this._evntElMouseOut) {
      this._el.removeEventListener('mouseout', this._evntElMouseOut)
      this._evntElMouseOut = null
    }

    if (this._evntWindowResize) {
      window.removeEventListener('resize', this._evntWindowResize)
      this._evntWindowResize = null
    }

    if (this._evntBodyClick) {
      document.body.removeEventListener('click', this._evntBodyClick)
      this._evntBodyClick = null
    }

    if (this._evntInnerMousemove) {
      this._inner && this._inner.removeEventListener('mousemove', this._evntInnerMousemove)
      this._evntInnerMousemove = null
    }

    if (this._evntInnerMouseleave) {
      this._inner && this._inner.removeEventListener('mouseleave', this._evntInnerMouseleave)
      this._evntInnerMouseleave = null
    }
  }

  // ─────────────────────────────────────────────────────────────
  // STATIC HELPERS  (unchanged from original)
  // ─────────────────────────────────────────────────────────────

  static _isRoot(item) {
    return !Menu._findParent(item, 'menu-item', false)
  }

  static _findParent(el, cls, throwError = true) {
    if (el.tagName.toUpperCase() === 'BODY') return null
    el = el.parentNode
    while (el.tagName.toUpperCase() !== 'BODY' && !el.classList.contains(cls)) {
      el = el.parentNode
    }

    el = el.tagName.toUpperCase() !== 'BODY' ? el : null

    if (!el && throwError) throw new Error(`Cannot find \`.${cls}\` parent element`)

    return el
  }

  static _findChild(el, cls) {
    const items = el.childNodes
    const found = []

    for (let i = 0, l = items.length; i < l; i++) {
      if (items[i].classList) {
        let passed = 0
        for (let j = 0; j < cls.length; j++) {
          if (items[i].classList.contains(cls[j])) passed += 1
        }
        if (cls.length === passed) found.push(items[i])
      }
    }

    return found
  }

  static _findMenu(item) {
    let curEl = item.childNodes[0]
    let menu = null

    while (curEl && !menu) {
      if (curEl.classList && curEl.classList.contains('menu-sub')) menu = curEl
      curEl = curEl.nextSibling
    }

    if (!menu) throw new Error('Cannot find `.menu-sub` element for the current `.menu-toggle`')

    return menu
  }

  static _hasClass(cls, el = window.Helpers.ROOT_EL) {
    let result = false
    cls.split(' ').forEach(c => {
      if (el.classList.contains(c)) result = true
    })
    return result
  }

  // ─────────────────────────────────────────────────────────────
  // OPEN / CLOSE / TOGGLE  (unchanged from original)
  // ─────────────────────────────────────────────────────────────

  open(el, closeChildren = this._closeChildren) {
    const item = this._findUnopenedParent(Menu._getItem(el, true), closeChildren)

    if (!item) return

    const toggleLink = Menu._getLink(item, true)

    Menu._promisify(this._onOpen, this, item, toggleLink, Menu._findMenu(item))
      .then(() => {
        if (!this._horizontal || !Menu._isRoot(item)) {
          if (this._animate && !this._horizontal) {
            window.requestAnimationFrame(() => this._toggleAnimation(true, item, false))
            if (this._accordion) this._closeOther(item, closeChildren)
          } else if (this._animate) {
            this._onOpened && this._onOpened(this, item, toggleLink, Menu._findMenu(item))
          } else {
            item.classList.add('open')
            this._onOpened && this._onOpened(this, item, toggleLink, Menu._findMenu(item))
            if (this._accordion) this._closeOther(item, closeChildren)
          }
        } else {
          this._onOpened && this._onOpened(this, item, toggleLink, Menu._findMenu(item))
        }
      })
      .catch(() => {})
  }

  close(el, closeChildren = this._closeChildren, _autoClose = false) {
    const item = Menu._getItem(el, true)
    const toggleLink = Menu._getLink(el, true)

    if (!item.classList.contains('open') || item.classList.contains('disabled')) return

    Menu._promisify(this._onClose, this, item, toggleLink, Menu._findMenu(item), _autoClose)
      .then(() => {
        if (!this._horizontal || !Menu._isRoot(item)) {
          if (this._animate && !this._horizontal) {
            window.requestAnimationFrame(() => this._toggleAnimation(false, item, closeChildren))
          } else {
            item.classList.remove('open')

            if (closeChildren) {
              const opened = item.querySelectorAll('.menu-item.open')
              for (let i = 0, l = opened.length; i < l; i++) opened[i].classList.remove('open')
            }

            this._onClosed && this._onClosed(this, item, toggleLink, Menu._findMenu(item))
          }
        } else {
          this._onClosed && this._onClosed(this, item, toggleLink, Menu._findMenu(item))
        }
      })
      .catch(() => {})
  }

  _closeOther(item, closeChildren) {
    const opened = Menu._findChild(item.parentNode, ['menu-item', 'open'])
    for (let i = 0, l = opened.length; i < l; i++) {
      if (opened[i] !== item) this.close(opened[i], closeChildren)
    }
  }

  toggle(el, closeChildren = this._closeChildren) {
    const item = Menu._getItem(el, true)
    if (item.classList.contains('open')) this.close(item, closeChildren)
    else this.open(item, closeChildren)
  }

  static _getItem(el, toggle) {
    let item = null
    const selector = toggle ? 'menu-toggle' : 'menu-link'

    if (el.classList.contains('menu-item')) {
      if (Menu._findChild(el, [selector]).length) item = el
    } else if (el.classList.contains(selector)) {
      item = el.parentNode.classList.contains('menu-item') ? el.parentNode : null
    }

    if (!item) throw new Error(`${toggle ? 'Toggable ' : ''}\`.menu-item\` element not found.`)

    return item
  }

  static _getLink(el, toggle) {
    let found = []
    const selector = toggle ? 'menu-toggle' : 'menu-link'

    if (el.classList.contains(selector)) found = [el]
    else if (el.classList.contains('menu-item')) found = Menu._findChild(el, [selector])

    if (!found.length) throw new Error(`\`${selector}\` element not found.`)

    return found[0]
  }

  _findUnopenedParent(item, closeChildren) {
    let tree = []
    let parentItem = null

    while (item) {
      if (item.classList.contains('disabled')) {
        parentItem = null
        tree = []
      } else {
        if (!item.classList.contains('open')) parentItem = item
        tree.push(item)
      }
      item = Menu._findParent(item, 'menu-item', false)
    }

    if (!parentItem) return null
    if (tree.length === 1) return parentItem

    tree = tree.slice(0, tree.indexOf(parentItem))

    for (let i = 0, l = tree.length; i < l; i++) {
      tree[i].classList.add('open')

      if (this._accordion) {
        const openedItems = Menu._findChild(tree[i].parentNode, ['menu-item', 'open'])

        for (let j = 0, k = openedItems.length; j < k; j++) {
          if (openedItems[j] !== tree[i]) {
            openedItems[j].classList.remove('open')

            if (closeChildren) {
              const openedChildren = openedItems[j].querySelectorAll('.menu-item.open')
              for (let x = 0, z = openedChildren.length; x < z; x++) {
                openedChildren[x].classList.remove('open')
              }
            }
          }
        }
      }
    }

    return parentItem
  }

  // ─────────────────────────────────────────────────────────────
  // ANIMATION  (unchanged from original)
  // ─────────────────────────────────────────────────────────────

  _toggleAnimation(open, item, closeChildren) {
    const toggleLink = Menu._getLink(item, true)
    const menu = Menu._findMenu(item)

    Menu._unbindAnimationEndEvent(item)

    const linkHeight = Math.round(toggleLink.getBoundingClientRect().height)

    item.style.overflow = 'hidden'

    const clearItemStyle = () => {
      item.classList.remove('menu-item-animating')
      item.classList.remove('menu-item-closing')
      item.style.overflow = null
      item.style.height = null
      this.update()
    }

    if (open) {
      item.style.height = `${linkHeight}px`
      item.classList.add('menu-item-animating')
      item.classList.add('open')

      Menu._bindAnimationEndEvent(item, () => {
        clearItemStyle()
        this._onOpened(this, item, toggleLink, menu)
      })

      setTimeout(() => {
        item.style.height = `${linkHeight + Math.round(menu.getBoundingClientRect().height)}px`
      }, 50)
    } else {
      item.style.height = `${linkHeight + Math.round(menu.getBoundingClientRect().height)}px`
      item.classList.add('menu-item-animating')
      item.classList.add('menu-item-closing')

      Menu._bindAnimationEndEvent(item, () => {
        item.classList.remove('open')
        clearItemStyle()

        if (closeChildren) {
          const opened = item.querySelectorAll('.menu-item.open')
          for (let i = 0, l = opened.length; i < l; i++) opened[i].classList.remove('open')
        }

        this._onClosed(this, item, toggleLink, menu)
      })

      setTimeout(() => {
        item.style.height = `${linkHeight}px`
      }, 50)
    }
  }

  static _bindAnimationEndEvent(el, handler) {
    const cb = e => {
      if (e.target !== el) return
      Menu._unbindAnimationEndEvent(el)
      handler(e)
    }

    let duration = window.getComputedStyle(el).transitionDuration
    duration = parseFloat(duration) * (duration.indexOf('ms') !== -1 ? 1 : 1000)

    el._menuAnimationEndEventCb = cb
    TRANSITION_EVENTS.forEach(ev => el.addEventListener(ev, el._menuAnimationEndEventCb, false))

    el._menuAnimationEndEventTimeout = setTimeout(() => {
      cb({ target: el })
    }, duration + 50)
  }

  static _unbindAnimationEndEvent(el) {
    const cb = el._menuAnimationEndEventCb

    if (el._menuAnimationEndEventTimeout) {
      clearTimeout(el._menuAnimationEndEventTimeout)
      el._menuAnimationEndEventTimeout = null
    }

    if (!cb) return

    TRANSITION_EVENTS.forEach(ev => el.removeEventListener(ev, cb, false))
    el._menuAnimationEndEventCb = null
  }

  // ─────────────────────────────────────────────────────────────
  // MISC  (unchanged from original)
  // ─────────────────────────────────────────────────────────────

  _getItemOffset(item) {
    let curItem = this._inner.childNodes[0]
    let left = 0

    while (curItem !== item) {
      if (curItem.tagName) left += Math.round(curItem.getBoundingClientRect().width)
      curItem = curItem.nextSibling
    }

    return left
  }

  static _promisify(fn, ...args) {
    const result = fn(...args)
    if (result instanceof Promise) return result
    if (result === false) return Promise.reject()
    return Promise.resolve()
  }

  get _innerWidth() {
    const items = this._inner.childNodes
    let width = 0
    for (let i = 0, l = items.length; i < l; i++) {
      if (items[i].tagName) width += Math.round(items[i].getBoundingClientRect().width)
    }
    return width
  }

  get _innerPosition() {
    return parseInt(this._inner.style[this._rtl ? 'marginRight' : 'marginLeft'] || '0px', 10)
  }

  set _innerPosition(value) {
    this._inner.style[this._rtl ? 'marginRight' : 'marginLeft'] = `${value}px`
    return value
  }

  closeAll(closeChildren = this._closeChildren) {
    const opened = this._el.querySelectorAll('.menu-inner > .menu-item.open')
    for (let i = 0, l = opened.length; i < l; i++) this.close(opened[i], closeChildren)
  }

  static setDisabled(el, disabled) {
    Menu._getItem(el, false).classList[disabled ? 'add' : 'remove']('disabled')
  }

  static isActive(el)    { return Menu._getItem(el, false).classList.contains('active') }
  static isOpened(el)    { return Menu._getItem(el, false).classList.contains('open') }
  static isDisabled(el)  { return Menu._getItem(el, false).classList.contains('disabled') }

  update() {
    if (this._scrollbar) this._scrollbar.update()
  }

  manageScroll() {
    const { PerfectScrollbar } = window
    const menuInner = document.querySelector('.menu-inner')

    if (window.innerWidth < window.Helpers.LAYOUT_BREAKPOINT) {
      if (this._scrollbar !== null) {
        this._scrollbar.destroy()
        this._scrollbar = null
      }
      menuInner.classList.add('overflow-auto')
    } else {
      if (this._scrollbar === null) {
        const menuScroll = new PerfectScrollbar(document.querySelector('.menu-inner'), {
          suppressScrollX: true,
          wheelPropagation: false
        })
        this._scrollbar = menuScroll
      }
      menuInner.classList.remove('overflow-auto')
    }
  }

  destroy() {
    if (!this._el) return

    this._unbindEvents()

    const items = this._el.querySelectorAll('.menu-item')
    for (let i = 0, l = items.length; i < l; i++) {
      Menu._unbindAnimationEndEvent(items[i])
      items[i].classList.remove('menu-item-animating')
      items[i].classList.remove('open')
      items[i].style.overflow = null
      items[i].style.height = null
    }

    const menus = this._el.querySelectorAll('.menu-menu')
    for (let i2 = 0, l2 = menus.length; i2 < l2; i2++) {
      menus[i2].style.marginRight = null
      menus[i2].style.marginLeft = null
    }

    this._el.classList.remove('menu-no-animation')

    if (this._wrapper) {
      this._prevBtn.parentNode.removeChild(this._prevBtn)
      this._nextBtn.parentNode.removeChild(this._nextBtn)
      this._wrapper.parentNode.insertBefore(this._inner, this._wrapper)
      this._wrapper.parentNode.removeChild(this._wrapper)
      this._inner.style.marginLeft = null
      this._inner.style.marginRight = null
    }

    // Clean up collapse state from DOM
    window.Helpers.ROOT_EL.classList.remove('layout-menu-collapsed', 'layout-menu-hover')

    this._el.menuInstance = null
    delete this._el.menuInstance

    this._el           = null
    this._animate      = null
    this._accordion    = null
    this._closeChildren = null
    this._onOpen       = null
    this._onOpened     = null
    this._onClose      = null
    this._onClosed     = null
    this._collapsed    = null
    this._hovered      = null

    if (this._scrollbar) {
      this._scrollbar.destroy()
      this._scrollbar = null
    }

    this._inner   = null
    this._prevBtn = null
    this._wrapper = null
    this._nextBtn = null
  }
}

window.Menu = Menu
export { Menu }
