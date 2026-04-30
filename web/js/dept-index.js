document.addEventListener('DOMContentLoaded', () => {
    const modalEl = document.getElementById('detailsModal');

    modalEl.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget;
        const deptID = button.getAttribute('data-id');

        fetch(getDeptDetailUrl + '&id=' + deptID)
            .then(response => response.json())
            .then(detail => {
                document.getElementById("detailsDeptName").value = detail.DeptName;
            })
            .catch(err => console.error(err));
    });

    window.toggleExpand = function(cardEl, deptID) {
        const wrapper = cardEl.closest('.dept-wrapper');
        const row = cardEl.closest('.row');
        const affiliatedUsersContainer = wrapper.querySelector('.aff-users-container');

        const isExpanded = wrapper.classList.contains('expanded');

        // reset everything first
        document.querySelectorAll('.dept-wrapper').forEach(el => {
            el.classList.remove('expanded');
        });
        row.classList.remove('expanded-mode');

        if (isExpanded) {
            affiliatedUsersContainer.innerHTML = ''; // ✅ clear ONLY this card
            return;
        }

        // if it was NOT expanded → expand it
        if (!isExpanded) {
            wrapper.classList.add('expanded');
            row.classList.add('expanded-mode');
        }

        fetch(getUsersAffiliatedUrl + '&id=' + deptID)
            .then(response => response.json())
            .then(results => {
                const affiliatedUsers = results.flat();

                console.log(affiliatedUsersContainer)

                affiliatedUsers.forEach(r => {
                    console.log(r.Userid);

                    affiliatedUsersContainer.insertAdjacentHTML('beforeend', `
                        <div class="col-md-4 col-sm-6 mb-3">
                        <div class="card bg-gradient-gray text-black shadow-sm aff-users-card h-100">
                            <div class="card-body d-flex flex-column">
                                <!-- TOP: icon + name -->
                                <div class="d-flex align-items-center mb-2">
                                    <i class="fas fa-user me-2"></i>
                                    <span class="fw-semibold fs-5">#${r.Userid}  -  ${r.Username}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    `);
                });
            })
            .catch(err => console.error(err));
    }


});