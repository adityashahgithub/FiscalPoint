<div>
            <h3>Your Budget for <?php echo $currentMonth; ?>:</h3>
          
                <p><?php echo $monthly_budget; ?></p>
            </div>
</div>
            <div>
            <h3 >Your income for <?php echo $currentMonth; ?>:</h3>
            <div>
                <p><?php echo $monthly_income; ?></p>
            </div>
        </div><br>

</div>
        <div >
            <h3>Today's Expense:</h3>
            <div>
                <p style="text-align: center; font-weight: bold;"><?php echo $today_expense; ?></p>
            </div>
        </div>
</div>
<div class="expense-box">
            <h3>Yesterday's Expense:</h3>
            <div<?php echo $yesterday_expense; ?></div>
        </div>

        <div class="expense-box">
            <h3>Monthly Expense:</h3>
            <div  <?php echo $expense_color; ?>;>
                <?php echo $monthly_expense; ?>
            </div>
        </div>

        <div >
            <h3>This Year Expense:</h3>
            <div><?php echo $yearly_expense; ?></div>
        </div>