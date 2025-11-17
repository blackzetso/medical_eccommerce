import { usePage } from '@inertiajs/vue3'

export function useTranslations() {
  const page = usePage()
  const t = (key) => {
    const dict = page?.props?.translations || {}
    return dict[key] || key
  }
  return { t }
}
