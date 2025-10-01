export const numberFormatter = (val, type = 'money', options = {}) => {
  const locale = options.locale ?? 'es-PE'

  switch (type) {
    case 'money':
      return ` ${Number(val).toLocaleString(locale, {
        style: 'currency',
        currency: options.currency ?? 'PEN',
      })}`

    case 'count':
      return ` ${Number(val).toLocaleString(locale)}`

    case 'percent':
      return `${Number(val).toLocaleString(locale)}%`

    default:
      return val
  }
}
