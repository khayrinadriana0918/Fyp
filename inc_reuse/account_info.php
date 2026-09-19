            <!-- =================================================
             USER INFORMATION
        ================================================== -->

            <section class="account-section">


                <h2>
                    Account Information
                </h2>

                <div class="account-info">
                    <!-- USER ID -->
                    <div>
                        <span>User ID</span>
                        <strong><?= htmlspecialchars($userInfo['user_id']); ?></strong>
                    </div>

                    <!-- ROLE ID -->
                    <div>
                        <span><?= htmlspecialchars($roleIdLabel); ?></span>
                        <strong><?= htmlspecialchars($userInfo['role_id']); ?></strong>
                    </div>

                    <!-- FULL NAME -->
                    <div>
                        <span>Full Name</span>
                        <strong><?= htmlspecialchars($userInfo['name']); ?></strong>
                    </div>

                    <!-- ACCOUNT CREATED -->
                    <div>
                        <span>Account Created</span>
                        <strong>
                            <?php
                            echo htmlspecialchars(
                                date(
                                    'd M Y',
                                    strtotime(
                                        $userInfo['created_at']
                                    )
                                )
                            );
                            ?>
                        </strong>
                    </div>
                </div>
            </section>