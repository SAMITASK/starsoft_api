export function useStatus() {
  const status = ref([
    { value: "00", title: "EMITIDA", color: "secondary", icon: "ri-mail-send-fill" },
    { value: "01", title: "APROBADA", color: "primary", icon: "ri-file-check-line" },
    { value: "03", title: "REC. PARCIALMENTE", color: "warning", icon: "ri-time-line" },
    { value: "04", title: "REC. TOTALMENTE", color: "success", icon: "ri-check-line" },
    { value: "05", title: "LIQUIDADA", color: "info", icon: "ri-cash-line" },
    { value: "06", title: "ANULADA", color: "error", icon: "ri-close-circle-line" },
  ]);

  const getStatusByValue = (value) => {
    return status.value.find(s => s.value === value) || null
  }

  return { status, getStatusByValue }
}
