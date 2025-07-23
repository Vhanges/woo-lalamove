<template>
  <div class="export-excel">
    <span @click="toggleExport" class="export-label">Export to Excel</span>
  </div>
</template>

<script setup>
import ExcelJS from 'exceljs'
import { saveAs } from 'file-saver'

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  filename: {
    type: String,
    default: 'lalamove_dashboard_data.xlsx'
  }
})

// Triggered on click
function toggleExport() {
  if (props.data) {
    console.log("Exporting data", props.data)
    exportToExcel(props.data)
  } else {
    console.warn('No data to export.')
  }
}

// Map for readable column names
const columnLabelMap = {

  //Column Name for Chart and KPI

  chartLabel: 'Date',
  motorcycleCount: 'Motorcycle Count',
  motorVehicleCount: 'Motor Vehicle Count',
  vanCount: 'Van Count',
  heavyTruckCount: 'Heavy Truck Count',
  truckCount: 'Truck Count',
  totalSpending: 'Total Spending',
  netSpending: 'Net Spending',
  customerSpent: 'Customer Spent',
  baseDeliveryCost: 'Base Delivery Cost',
  subsidySpent: 'Subsidy Spent',
  priorityFee: 'Priority Fee',
  surcharge: 'Surcharge',
  walletBalance: 'Wallet Balance',
  transactionId: 'Transaction ID',
  customerName: 'Customer Name',
  amount: 'Amount',
  date: 'Date',
  status: 'Status',

  //Column Name for Transactions
  lalaID: 'Lalamove Order ID',
  wooID: 'Woocommerce Order ID',
  orderedBy: 'Ordered By',
  orderedOn: 'Ordered On',
  overallExpense: 'Overall Expense',
  paymentMethod: 'Payment Method',
  serviceType: 'Service Type',
  statusName: 'Status',

  //Column Name for Records
  dropOffLocation: 'Drop Off Location',
  lalamoveOrderId: 'Lalamove Order ID',
  orderJsonBody: 'Order JSON Body',
  orderedBy: 'Ordered By',
  orderedOn: 'Ordered On',
  scheduledOn: 'Scheduled On',
  serviceType: 'Service Type',
  statusName: 'Status Name',
  wcOrderId: 'WooCommerce Order ID',




}


// Main Excel export function
async function exportToExcel(data) {
  const workbook = new ExcelJS.Workbook();

  try {
    if (data.spendingKPI?.length > 0) {
      const kpiSheet = workbook.addWorksheet('Spending KPI Report');
      addStyledTable(kpiSheet, data.spendingKPI, 'Spending KPI Report');
    }

    if (data.spendingChartData?.length > 0) {
      const chartSheet = workbook.addWorksheet('Spending Chart Report');
      addStyledTable(chartSheet, data.spendingChartData, 'Spending Chart Report');
    }

    if (data.ordersKPI?.length > 0) {
      const kpiSheet = workbook.addWorksheet('Orders KPI Report');
      addStyledTable(kpiSheet, data.ordersKPI, 'Orders KPI Report');
    }

    if (data.RecordsReport?.length > 0) {
      const recordsReport = workbook.addWorksheet('Records Report');
      addStyledTable(recordsReport, data.RecordsReport, 'Records Report');
    }

    if (data.ordersChartData?.length > 0) {
      const chartSheet = workbook.addWorksheet('Orders Chart Report');
      addStyledTable(chartSheet, data.ordersChartData, 'Orders Chart Report');
    }

    if (data.TransactionData?.length > 0) { 
      const transactionSheet = workbook.addWorksheet('Transaction Data');
      addStyledTable(transactionSheet, data.TransactionData, 'Transaction Report');
    }
    
    if (data.OrdersData?.length > 0) {
      const transactionSheet = workbook.addWorksheet('Orders Data');
      addStyledTable(transactionSheet, data.OrdersData, 'Orders Report');
    }

    if (workbook.worksheets.length === 0) {
      workbook.addWorksheet('Info').addRow(["No data available to export"]);
    }

    // Generate and save the Excel file
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    });
    saveAs(blob, props.filename);
  } catch (error) {
    console.error('Export error:', error);
  }
}

function addStyledTable(worksheet, rows, sheetTitle) {
  if (!rows?.length) {
    worksheet.addRow(["No data available"]);
    return;
  }

// ======================
  // 1. Title Section Styling
  // ======================
  const headers = Object.keys(rows[0] || {}); // Fixed missing parenthesis
  const lastColumn = String.fromCharCode(64 + headers.length);

  // Add main title
  const titleRow = worksheet.addRow([sheetTitle]);
  titleRow.font = { 
    bold: true, 
    size: 16, 
    color: { argb: 'FF2F5496' }
  };
  titleRow.alignment = { vertical: 'middle', horizontal: 'center' };
  worksheet.mergeCells(`A1:${lastColumn}1`);

  // Add subtitle
  const subtitleRow = worksheet.addRow([`Generated on ${new Date().toLocaleDateString()}`]);
  subtitleRow.font = { 
    italic: true, 
    color: { argb: 'FF808080' }
  };
  worksheet.mergeCells(`A2:${lastColumn}2`);
  worksheet.addRow([]);

  // ======================
  // 2. Header Row Styling
  // ======================
  const readableHeaders = headers.map(h => columnLabelMap[h] || 
    h.replace(/([A-Z])/g, ' $1').trim());

  const headerRow = worksheet.addRow(readableHeaders);
  headerRow.eachCell(cell => {
    cell.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'F16622' }
    };
    cell.font = { 
      bold: true, 
      color: { argb: 'FFFFFFFF' },
      size: 12
    };
    cell.border = {
      top: { style: 'thin', color: { argb: 'FF000000' } },
      bottom: { style: 'thin', color: { argb: 'FF000000' } },
      left: { style: 'thin', color: { argb: 'FFD3D3D3' } },
      right: { style: 'thin', color: { argb: 'FFD3D3D3' } }
    };
    cell.alignment = { 
      vertical: 'middle', 
      horizontal: 'center',
      wrapText: true
    };
  });

  // ======================
  // 3. Data Rows Styling
  // ======================

  const textColumns = ['lalaID', 'wooID'];

  rows.forEach((row, index) => {
    const convertedRow = {};
    for (const [key, value] of Object.entries(row)) {
      convertedRow[key] = isNaN(Number(value)) ? value : Number(value);
    }
    
    const dataRow = worksheet.addRow(headers.map(h => convertedRow[h]));
    
    // Alternate row colors
    dataRow.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: index % 2 === 0 ? 'FFFFFFFF' : 'FFF2F2F2' }
    };

    // Cell formatting
    dataRow.eachCell((cell, colNumber) => {
      const headerKey = headers[colNumber - 1];
      cell.border = {
        bottom: { style: 'thin', color: { argb: 'FFD3D3D3' } },
        right: { style: 'thin', color: { argb: 'FFD3D3D3' } }
      };
      
      // Numeric formatting
      if (typeof convertedRow[headerKey] === 'number') {
        cell.numFmt =
         headerKey.toLowerCase().includes('spent') || 
         headerKey.toLowerCase().includes('spending') || 
         headerKey.toLowerCase().includes('balance') || 
         headerKey.toLowerCase().includes('surcharge') || 
         headerKey.toLowerCase().includes('fee') || 
         headerKey.toLowerCase().includes('cost') ? 
          '"₱"#,##0.00' : '0';
        cell.alignment = { horizontal: 'center' };
      }
      
      // Date formatting
      if (headerKey.toLowerCase().includes('date')) {
        cell.numFmt = 'dd-mmm-yyyy';
      }
    });
  });

  // ======================
  // 4. Advanced Features
  // ======================
  // Conditional formatting
  const statusColumn = headers.findIndex(h => h.toLowerCase().includes('status')) + 1;
  if (statusColumn > 0) {
    worksheet.addConditionalFormatting({
      ref: `${String.fromCharCode(64 + statusColumn)}4:${String.fromCharCode(64 + statusColumn)}${rows.length + 3}`,
      rules: [
        {
          type: 'containsText',
          operator: 'containsText',
          text: 'Completed',
          style: { fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFC6EFCE' } } }
        },
        {
          type: 'containsText',
          operator: 'containsText',
          text: 'Pending',
          style: { fill: { type: 'pattern', pattern: 'solid', fgColor: { argb: 'FFFFE699' } } }
        }
      ]
    });
  }

  // Freeze panes
  worksheet.views = [{
    state: 'frozen',
    ySplit: 3 // Freeze title + headers
  }];

  // Auto column widths
  worksheet.columns.forEach(column => {
    let maxLength = 15;
    column.eachCell({ includeEmpty: true }, cell => {
      const cellValue = cell.text ? cell.text.toString() : '';
      maxLength = Math.max(maxLength, cellValue.length);
    });
    column.width = Math.min(Math.max(maxLength + 2, 10), 30);
  });

  // Add filters
  worksheet.autoFilter = {
    from: { row: 3, column: 1 },
    to: { row: 3, column: headers.length }
  };

  // Set print settings
  worksheet.pageSetup = {
    orientation: 'landscape',
    margins: {
      left: 0.7, right: 0.7,
      top: 0.75, bottom: 0.75,
      header: 0.3, footer: 0.3
    },
    fitToPage: true,
    fitToWidth: 1,
    fitToHeight: 0,
    paperSize: 9 
  };
}
</script>

<style lang="scss" scoped>
@use '@/css/scss/_variables.scss' as *;

.export-excel {
  display: flex;
  flex-direction: column;
  height: 2rem;
  width: 10rem;
  gap: 0.5rem;
  cursor: pointer;
  user-select: none;
  border: 1px solid $border-color;
  border-radius: 5px;
  background-color: $bg-high-light;
}

.export-label {
  height: 100%;
  display: flex;
  align-items: center;
  padding: 3%;
  border-radius: 3%;
  background-color: $bg-high-light;
  font-size: $font-size-sm;
}
</style>
