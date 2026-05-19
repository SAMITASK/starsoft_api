const normalizeCompanyId = value => {
  if (value === null || value === undefined || value === '')
    return null

  return `${value}`
}

const uniqueIds = values => [...new Set(values.filter(Boolean))]

const getAllowedCompanyIds = userData => uniqueIds(
  Array.isArray(userData?.company_ids)
    ? userData.company_ids.map(normalizeCompanyId)
    : [],
)

const getCompanyIds = (companies = [], getId) => uniqueIds(
  Array.isArray(companies)
    ? companies
      .map(company => normalizeCompanyId(typeof getId === 'function'
        ? getId(company)
        : company?.id ?? company?.value ?? company))
    : [],
)

export const resolveCompanySelection = ({
  currentCompany = null,
  userData = null,
  companies = [],
  getId,
} = {}) => {
  const currentId = normalizeCompanyId(currentCompany)
  const defaultId = normalizeCompanyId(userData?.company_default)
  const allowedIds = getAllowedCompanyIds(userData)
  const companyIds = getCompanyIds(companies, getId)

  const isValidCompany = companyId => {
    if (!companyId)
      return false

    const hasAccess = !allowedIds.length || allowedIds.includes(companyId)
    const existsInLoadedCompanies = !companyIds.length || companyIds.includes(companyId)

    return hasAccess && existsInLoadedCompanies
  }

  if (isValidCompany(currentId))
    return currentId

  if (isValidCompany(defaultId))
    return defaultId

  const firstAllowedCompany = allowedIds.find(companyId => !companyIds.length || companyIds.includes(companyId))

  if (firstAllowedCompany)
    return firstAllowedCompany

  return companyIds[0] ?? null
}

export const syncSelectedCompany = (selectedCompany, userData, companies = [], getId) => {
  const nextCompany = resolveCompanySelection({
    currentCompany: selectedCompany.value,
    userData,
    companies,
    getId,
  })

  if (normalizeCompanyId(selectedCompany.value) !== normalizeCompanyId(nextCompany))
    selectedCompany.value = nextCompany

  return nextCompany
}
