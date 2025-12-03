import { usePage } from '@inertiajs/vue3'

export function useTranslations() {
  const page = usePage()
  const t = (key) => {
    const dict = page?.props?.translations || {}
    const translation = dict[key]
    
    // Debug in development
    if (import.meta.env.DEV) {
      if (!translation && key) {
        console.warn(`Translation key not found: "${key}"`, {
          availableKeys: Object.keys(dict).slice(0, 10),
          totalKeys: Object.keys(dict).length
        })
      }
    }
    
    return translation || key
  }
  return { t }
}
